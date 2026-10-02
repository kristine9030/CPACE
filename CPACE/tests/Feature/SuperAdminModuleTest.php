<?php

namespace Tests\Feature;

use App\Mail\AccountCredentialsMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers the Super Admin console: role-gated access, account provisioning
 * across every role (mirrors ChairAccountProvisioningTest's OTP-by-email
 * contract), and the overall activity log Auditor::log() writes to.
 * Hand-built schema, same rationale as the rest of this suite (migration
 * set doesn't run cleanly from scratch against the real DB dump).
 */
class SuperAdminModuleTest extends TestCase
{
    private const TABLES = [
        'activity_logs', 'ai_usage_logs', 'faculty_profiles', 'student_profiles',
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
        });
        Schema::create('faculty_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('employee_number', 20)->nullable();
            $table->string('department', 100)->nullable();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('actor_role', 20)->nullable();
            $table->string('action', 60);
            $table->text('description')->nullable();
            $table->string('subject_type', 60)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('feature', 60);
            $table->string('provider', 30)->nullable();
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

    public function test_a_non_super_admin_is_blocked_from_the_console(): void
    {
        $chair = $this->makeUser(Role::ADMIN);

        $this->actingAs($chair)->get(route('superadmin.dashboard'))->assertForbidden();
    }

    public function test_a_super_admin_can_view_the_dashboard(): void
    {
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);
        $this->makeUser(Role::STUDENT, 'student1@example.com');
        $this->makeUser(Role::FACULTY, 'faculty1@example.com');

        // Route-tagged duration samples (RecordRequestMetrics), same shape the
        // dashboard's own "Avg Response Time"/"p95" cards read from — a plain
        // array_sum() over these (without pulling out 'ms' first) is what broke
        // the dashboard with a 500 right after that change shipped.
        Cache::put('metrics.durations', [
            ['route' => 'dashboard', 'ms' => 100],
            ['route' => 'dashboard', 'ms' => 200],
        ], now()->addDay());

        $this->actingAs($superAdmin)->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertViewHas('userStats', fn ($stats) => $stats['total'] === 3 && $stats['students'] === 1 && $stats['faculty'] === 1)
            ->assertViewHas('performance', fn ($performance) => $performance['avg_response_ms'] === 150
                && $performance['sample_size'] === 2);
    }

    public function test_super_admin_can_create_a_faculty_account_and_the_otp_never_leaks_into_the_flash_message(): void
    {
        Mail::fake();
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);

        $response = $this->actingAs($superAdmin)->post(route('superadmin.users.store'), [
            'first_name' => 'New',
            'last_name' => 'Faculty',
            'email' => 'newfaculty@example.com',
            'role_id' => Role::FACULTY,
            'department' => 'College of Accountancy',
        ]);

        $response->assertRedirect(route('superadmin.users'));

        $user = User::where('email', 'newfaculty@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(Role::FACULTY, $user->role_id);
        $this->assertTrue($user->is_active);
        $this->assertDatabaseHas('faculty_profiles', ['user_id' => $user->id]);

        Mail::assertSent(AccountCredentialsMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->tempPassword !== '' && $mail->roleLabel === 'Faculty';
        });

        $status = session('status');
        $this->assertStringNotContainsString($user->temp_password, (string) $status);
    }

    public function test_super_admin_can_create_another_super_admin_account(): void
    {
        Mail::fake();
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);

        $this->actingAs($superAdmin)->post(route('superadmin.users.store'), [
            'first_name' => 'Second',
            'last_name' => 'Admin',
            'email' => 'second-admin@example.com',
            'role_id' => Role::SUPER_ADMIN,
        ])->assertRedirect(route('superadmin.users'));

        $this->assertDatabaseHas('users', ['email' => 'second-admin@example.com', 'role_id' => Role::SUPER_ADMIN]);
    }

    public function test_a_super_admin_cannot_deactivate_their_own_account(): void
    {
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);

        $this->actingAs($superAdmin)
            ->post(route('superadmin.users.toggle-active', $superAdmin->id))
            ->assertRedirect();

        $this->assertTrue($superAdmin->fresh()->is_active);
    }

    public function test_login_and_logout_are_recorded_in_the_activity_log_and_visible_to_the_super_admin(): void
    {
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);
        $student = $this->makeUser(Role::STUDENT, 'logstudent@example.com', 'password123');

        $this->post(route('login'), ['email' => 'logstudent@example.com', 'password' => 'password123']);
        $this->post(route('logout'));

        $this->assertDatabaseHas('activity_logs', ['actor_id' => $student->id, 'action' => 'login']);
        $this->assertDatabaseHas('activity_logs', ['actor_id' => $student->id, 'action' => 'logout']);

        $this->actingAs($superAdmin)->get(route('superadmin.activity-log', ['action' => 'login']))
            ->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->total() >= 1);
    }

    public function test_a_non_super_admin_is_blocked_from_the_performance_page(): void
    {
        $chair = $this->makeUser(Role::ADMIN);

        $this->actingAs($chair)->get(route('superadmin.performance'))->assertForbidden();
    }

    public function test_the_performance_page_reflects_cache_backed_traffic_metrics(): void
    {
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);
        $today = now()->format('Y-m-d');

        Cache::put("metrics.requests.{$today}", 50, now()->addDay());
        Cache::put("metrics.errors.{$today}", 1, now()->addDay());
        Cache::put("metrics.duration_sum.{$today}", 5000, now()->addDay());
        Cache::put("metrics.duration_count.{$today}", 50, now()->addDay());
        // Each sample is route-tagged (RecordRequestMetrics) so the page can
        // break the p95/p99 tail down by endpoint — mixing in one legacy
        // plain-int sample here too, to confirm older cached samples (from
        // before route tagging shipped) don't break the summary while their
        // 1-day TTL rolls off naturally.
        $samples = array_fill(0, 49, ['route' => 'dashboard', 'ms' => 100]);
        $samples[] = 100;
        Cache::put('metrics.durations', $samples, now()->addDay());
        // AI-calling routes (ai-tutor chat, ai-draft, gap-fill generate) are
        // tracked in their own bucket so their multi-second provider latency
        // doesn't skew the app's own response-time metrics above.
        Cache::put("metrics.ai_duration_sum.{$today}", 9000, now()->addDay());
        Cache::put("metrics.ai_duration_count.{$today}", 3, now()->addDay());
        Cache::put('metrics.ai_durations', [
            ['route' => 'ai-tutor.chat', 'ms' => 2000],
            ['route' => 'ai-tutor.chat', 'ms' => 3000],
            ['route' => 'chair.ai-review.generate', 'ms' => 4000],
        ], now()->addDay());

        $this->actingAs($superAdmin)->get(route('superadmin.performance'))
            ->assertOk()
            ->assertViewHas('summary', function ($summary) {
                return $summary['requests_today'] === 50
                    && $summary['errors_today'] === 1
                    && $summary['avg_response_ms'] === 100
                    && $summary['ai_avg_response_ms'] === 3000
                    && $summary['ai_sample_size'] === 3
                    && $summary['slow_routes'][0]['route'] === 'dashboard'
                    && $summary['slow_routes'][0]['count'] === 49
                    && collect($summary['ai_slow_routes'])->firstWhere('route', 'ai-tutor.chat')['avg_ms'] === 2500;
            })
            ->assertViewHas('trend', fn ($trend) => $trend->last()['requests'] === 50
                && $trend->last()['ai_avg_response_ms'] === 3000);
    }

    private function makeUser(int $roleId, string $email = 'user@example.com', string $password = 'password'): User
    {
        return User::create([
            'role_id' => $roleId,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'password' => Hash::make($password),
            'is_active' => true,
            'setup_completed_at' => now(),
        ]);
    }
}
