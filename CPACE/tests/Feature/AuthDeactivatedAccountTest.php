<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The Program Chair can deactivate a faculty (or any) login without deleting
 * it (users.is_active). Auth::attempt() only checks email/password, so the
 * login flow itself must reject a correct password on a deactivated account.
 */
class AuthDeactivatedAccountTest extends TestCase
{
    private const TABLES = ['users'];

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
            $table->rememberToken();
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

    public function test_a_deactivated_faculty_account_cannot_log_in_with_the_correct_password(): void
    {
        User::create([
            'role_id'    => Role::FACULTY,
            'first_name' => 'Test', 'last_name' => 'Faculty',
            'email'      => 'faculty@example.com',
            'password'   => Hash::make('correct-password'),
            'is_active'  => false,
        ]);

        $response = $this->post('/login', [
            'email'    => 'faculty@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_an_active_faculty_account_can_log_in_normally(): void
    {
        User::create([
            'role_id'    => Role::FACULTY,
            'first_name' => 'Test', 'last_name' => 'Faculty',
            'email'      => 'faculty@example.com',
            'password'   => Hash::make('correct-password'),
            'is_active'  => true,
            'setup_completed_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email'    => 'faculty@example.com',
            'password' => 'correct-password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('faculty.dashboard'));
    }
}
