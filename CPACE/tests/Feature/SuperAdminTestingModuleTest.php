<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers the Super Admin "Testing" section: live System Checks (DB, session
 * signing, email, AI providers), Test Reports (upload/parse/list/delete),
 * and Evaluations (Weakness Detection precision/recall/F1 vs baseline, and
 * SM-2 correctness against hand-derived expected values). Hand-built
 * schema, same rationale as the rest of this suite.
 */
class SuperAdminTestingModuleTest extends TestCase
{
    private const TABLES = [
        'test_reports', 'api_tokens', 'performance_records', 'notifications',
        'messages', 'conversation_participants', 'conversations', 'users',
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
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('topic_id');
            $table->unsignedSmallInteger('total_attempts')->default(0);
            $table->unsignedSmallInteger('correct_count')->default(0);
            $table->unsignedTinyInteger('consecutive_wrong')->default(0);
        });
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->string('name')->nullable();
            $table->string('token', 128)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
        Schema::create('test_reports', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('title');
            $table->json('summary')->nullable();
            $table->longText('raw_payload');
            $table->unsignedBigInteger('uploaded_by')->nullable();
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

    public function test_a_non_super_admin_is_blocked_from_every_testing_page(): void
    {
        $chair = $this->makeUser(Role::ADMIN);

        $this->actingAs($chair)->get(route('superadmin.system-checks'))->assertForbidden();
        $this->actingAs($chair)->get(route('superadmin.test-reports'))->assertForbidden();
        $this->actingAs($chair)->get(route('superadmin.evaluations'))->assertForbidden();
        $this->actingAs($chair)->get(route('superadmin.api-tokens'))->assertForbidden();
    }

    public function test_a_super_admin_can_issue_and_revoke_a_named_api_token(): void
    {
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);

        $response = $this->actingAs($superAdmin)->post(route('superadmin.api-tokens.store'), [
            'name' => 'GitHub Actions — Playwright',
        ]);
        $response->assertRedirect(route('superadmin.api-tokens'));
        $response->assertSessionHas('newToken');

        $this->assertDatabaseHas('api_tokens', ['user_id' => $superAdmin->id, 'name' => 'GitHub Actions — Playwright']);

        $token = \App\Models\ApiToken::first();
        $this->actingAs($superAdmin)->delete(route('superadmin.api-tokens.destroy', $token->id))->assertRedirect();
        $this->assertDatabaseMissing('api_tokens', ['id' => $token->id]);
    }

    public function test_the_test_reports_api_endpoint_accepts_a_valid_super_admin_token(): void
    {
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);
        $token = \App\Models\ApiToken::issueNamed($superAdmin->id, 'CI token');

        $response = $this->postJson('/api/test-reports', [
            'type' => 'frontend',
            'title' => 'CI upload',
            'payload' => ['stats' => ['expected' => 5, 'unexpected' => 0, 'skipped' => 0]],
        ], ['Authorization' => 'Bearer ' . $token->token]);

        $response->assertCreated();
        $response->assertJsonPath('recognized', true);
        $this->assertDatabaseHas('test_reports', ['title' => 'CI upload', 'type' => 'frontend']);
    }

    public function test_the_test_reports_api_endpoint_rejects_a_non_super_admin_token(): void
    {
        $student = $this->makeUser(Role::STUDENT, 'student2@example.com');
        $token = \App\Models\ApiToken::generate($student->id);

        $this->postJson('/api/test-reports', [
            'type' => 'frontend', 'title' => 'Should be rejected', 'payload' => ['stats' => []],
        ], ['Authorization' => 'Bearer ' . $token->token])->assertForbidden();
    }

    public function test_the_test_reports_api_endpoint_rejects_a_missing_token(): void
    {
        $this->postJson('/api/test-reports', [
            'type' => 'frontend', 'title' => 'No token', 'payload' => ['stats' => []],
        ])->assertUnauthorized();
    }

    public function test_system_checks_page_runs_every_check_and_reports_ai_provider_reachability(): void
    {
        // Force the "key configured" branch regardless of what's in .env —
        // CI's .env.example has no AI provider keys, so without this the
        // checks short-circuit to 'warn' before Http::fake() below ever
        // gets exercised.
        config([
            'services.gemini.key' => 'test-gemini-key',
            'services.openrouter.key' => 'test-openrouter-key',
            'services.anthropic.key' => 'test-anthropic-key',
        ]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['models' => []], 200),
            'openrouter.ai/*' => Http::response(['data' => []], 200),
            'api.anthropic.com/*' => Http::response(['data' => []], 401),
        ]);

        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);

        $this->actingAs($superAdmin)->get(route('superadmin.system-checks'))
            ->assertOk()
            ->assertViewHas('checks', function ($checks) {
                $byKey = collect($checks)->keyBy('key');

                return count($checks) === 6
                    && $byKey['database']['status'] === 'ok'
                    && $byKey['session']['status'] === 'ok'
                    && $byKey['gemini']['status'] === 'ok'
                    && $byKey['openrouter']['status'] === 'ok'
                    && $byKey['anthropic']['status'] === 'fail';
            });
    }

    public function test_uploading_a_recognized_playwright_report_computes_a_summary(): void
    {
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);
        $payload = json_encode(['stats' => ['expected' => 18, 'unexpected' => 2, 'skipped' => 1, 'flaky' => 0, 'duration' => 45210]]);

        $this->actingAs($superAdmin)->post(route('superadmin.test-reports.store'), [
            'type' => 'frontend',
            'title' => 'Nightly regression',
            'payload' => $payload,
        ])->assertRedirect(route('superadmin.test-reports', ['type' => 'frontend']));

        $this->assertDatabaseHas('test_reports', ['title' => 'Nightly regression', 'type' => 'frontend']);
        $report = \App\Models\TestReport::first();
        $this->assertTrue($report->summary['recognized']);
        $this->assertSame(18, $report->summary['passed']);
        $this->assertSame(2, $report->summary['failed']);
    }

    public function test_uploading_an_unrecognized_shape_still_stores_the_report_as_raw(): void
    {
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);

        $this->actingAs($superAdmin)->post(route('superadmin.test-reports.store'), [
            'type' => 'load',
            'title' => 'Ad-hoc run',
            'payload' => json_encode(['something' => 'unexpected']),
        ])->assertRedirect();

        $report = \App\Models\TestReport::first();
        $this->assertFalse($report->summary['recognized']);
    }

    public function test_invalid_json_is_rejected_with_a_validation_error(): void
    {
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);

        $response = $this->actingAs($superAdmin)->post(route('superadmin.test-reports.store'), [
            'type' => 'api',
            'title' => 'Broken upload',
            'payload' => '{not valid json',
        ]);

        $response->assertSessionHasErrors('payload');
        $this->assertDatabaseCount('test_reports', 0);
    }

    public function test_deleting_a_report_removes_it(): void
    {
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);
        $this->actingAs($superAdmin)->post(route('superadmin.test-reports.store'), [
            'type' => 'frontend', 'title' => 'To delete', 'payload' => json_encode(['stats' => ['expected' => 1, 'unexpected' => 0, 'skipped' => 0]]),
        ]);
        $report = \App\Models\TestReport::first();

        $this->actingAs($superAdmin)->delete(route('superadmin.test-reports.destroy', $report->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('test_reports', ['id' => $report->id]);
    }

    public function test_weakness_detection_evaluation_computes_precision_recall_f1_against_the_accuracy_baseline(): void
    {
        $student = $this->makeUser(Role::STUDENT, 'student@example.com');
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);

        // TP: module flags weak (low accuracy, enough attempts) and baseline agrees (accuracy < 60%).
        DB::table('performance_records')->insert(['student_id' => $student->id, 'topic_id' => 1, 'total_attempts' => 10, 'correct_count' => 2, 'consecutive_wrong' => 0]);
        // FP: module flags weak via the consecutive-wrong trigger, but accuracy is actually >= 60% (baseline disagrees).
        DB::table('performance_records')->insert(['student_id' => $student->id, 'topic_id' => 2, 'total_attempts' => 10, 'correct_count' => 7, 'consecutive_wrong' => 3]);
        // FN: baseline says weak (accuracy < 60%) but the module withholds the call — too few attempts to trust the rate.
        DB::table('performance_records')->insert(['student_id' => $student->id, 'topic_id' => 3, 'total_attempts' => 2, 'correct_count' => 0, 'consecutive_wrong' => 0]);
        // TN: both agree it's fine.
        DB::table('performance_records')->insert(['student_id' => $student->id, 'topic_id' => 4, 'total_attempts' => 10, 'correct_count' => 9, 'consecutive_wrong' => 0]);

        $this->actingAs($superAdmin)->get(route('superadmin.evaluations'))
            ->assertOk()
            ->assertViewHas('weakness', function ($w) {
                return $w['tp'] === 1 && $w['fp'] === 1 && $w['fn'] === 1 && $w['tn'] === 1
                    && $w['precision'] === 0.5 && $w['recall'] === 0.5 && $w['f1'] === 0.5;
            });
    }

    public function test_sm2_correctness_cases_all_pass_against_the_real_scheduler(): void
    {
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);

        $this->actingAs($superAdmin)->get(route('superadmin.evaluations'))
            ->assertOk()
            ->assertViewHas('sm2', fn ($sm2) => $sm2['passed'] === $sm2['total'] && $sm2['total'] > 0);
    }

    private function makeUser(int $roleId, string $email = 'user@example.com'): User
    {
        return User::create([
            'role_id' => $roleId,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'password' => Hash::make('password'),
            'is_active' => true,
            'setup_completed_at' => now(),
        ]);
    }
}
