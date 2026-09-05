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
 * Covers Alumni\ProfileController::update. Hand-built schema, same rationale
 * as the other Feature tests in this suite (migration set doesn't run
 * cleanly from scratch).
 */
class AlumniProfileTest extends TestCase
{
    private const TABLES = ['alumni_profiles', 'notifications', 'messages', 'conversation_participants', 'conversations', 'users'];

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
        Schema::create('alumni_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->year('batch_year')->nullable();
            $table->string('current_job')->nullable();
            $table->string('company')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->text('bio')->nullable();
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_an_alumnus_can_update_their_profile(): void
    {
        $alumnus = $this->alumnus();

        $this->actingAs($alumnus)->post(route('alumni.profile.update'), [
            'batch_year' => 2020,
            'current_job' => 'Senior Auditor',
            'company' => 'Big Four Firm',
            'linkedin_url' => 'https://linkedin.com/in/example',
            'bio' => 'Passed the CPALE in 2020.',
        ])->assertRedirect();

        $profile = DB::table('alumni_profiles')->where('user_id', $alumnus->id)->first();
        $this->assertSame(2020, $profile->batch_year);
        $this->assertSame('Senior Auditor', $profile->current_job);
    }

    public function test_updating_a_profile_twice_replaces_it_rather_than_creating_a_duplicate(): void
    {
        $alumnus = $this->alumnus();

        $this->actingAs($alumnus)->post(route('alumni.profile.update'), ['current_job' => 'Junior Auditor']);
        $this->actingAs($alumnus)->post(route('alumni.profile.update'), ['current_job' => 'Senior Auditor']);

        $this->assertSame(1, DB::table('alumni_profiles')->where('user_id', $alumnus->id)->count());
        $this->assertSame('Senior Auditor', DB::table('alumni_profiles')->where('user_id', $alumnus->id)->value('current_job'));
    }

    public function test_an_invalid_linkedin_url_is_rejected(): void
    {
        $alumnus = $this->alumnus();

        $this->actingAs($alumnus)->post(route('alumni.profile.update'), [
            'linkedin_url' => 'not-a-url',
        ])->assertSessionHasErrors('linkedin_url');

        $this->assertSame(0, DB::table('alumni_profiles')->where('user_id', $alumnus->id)->count());
    }

    private function alumnus(): User
    {
        return User::create([
            'role_id' => Role::ALUMNI,
            'first_name' => 'Test',
            'last_name' => 'Alumnus',
            'email' => 'alumnus@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'setup_completed_at' => now(),
        ]);
    }
}
