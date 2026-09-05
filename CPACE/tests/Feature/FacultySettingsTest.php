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
 * Hand-built schema, same rationale as the other Feature tests in this
 * suite (migration set doesn't run cleanly from scratch).
 */
class FacultySettingsTest extends TestCase
{
    private const TABLES = ['faculty_profiles', 'users'];

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
        Schema::create('faculty_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->string('employee_number', 20)->nullable();
            $table->string('department', 100)->nullable();
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_faculty_can_update_their_name_and_email(): void
    {
        $faculty = $this->faculty();

        $this->actingAs($faculty)->post(route('faculty.settings.profile'), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
        ])->assertRedirect();

        $fresh = $faculty->fresh();
        $this->assertSame('Updated', $fresh->first_name);
        $this->assertSame('updated@example.com', $fresh->email);
    }

    public function test_faculty_cannot_update_their_email_to_one_already_taken_by_someone_else(): void
    {
        $faculty = $this->faculty('me@example.com');
        $this->faculty('taken@example.com');

        $this->actingAs($faculty)->post(route('faculty.settings.profile'), [
            'first_name' => $faculty->first_name,
            'last_name' => $faculty->last_name,
            'email' => 'taken@example.com',
        ])->assertSessionHasErrors('email');

        $this->assertSame('me@example.com', $faculty->fresh()->email);
    }

    public function test_faculty_can_update_their_employee_details(): void
    {
        $faculty = $this->faculty();

        $this->actingAs($faculty)->post(route('faculty.settings.details'), [
            'employee_number' => 'EMP-001',
            'department' => 'CS Department',
        ])->assertRedirect();

        $this->assertSame('EMP-001', $faculty->facultyProfile()->first()->employee_number);
    }

    public function test_faculty_can_change_their_password_with_the_correct_current_password(): void
    {
        $faculty = $this->faculty(password: 'OldPassword1');

        $this->actingAs($faculty)->post(route('faculty.settings.password'), [
            'current_password' => 'OldPassword1',
            'new_password' => 'NewPassword1',
            'new_password_confirmation' => 'NewPassword1',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('NewPassword1', $faculty->fresh()->password));
    }

    public function test_changing_password_is_rejected_when_the_current_password_is_wrong(): void
    {
        $faculty = $this->faculty(password: 'OldPassword1');

        $this->actingAs($faculty)->post(route('faculty.settings.password'), [
            'current_password' => 'WrongPassword',
            'new_password' => 'NewPassword1',
            'new_password_confirmation' => 'NewPassword1',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('OldPassword1', $faculty->fresh()->password));
    }

    private function faculty(string $email = 'faculty@example.com', string $password = 'password'): User
    {
        $user = User::create([
            'role_id' => Role::FACULTY,
            'first_name' => 'Test',
            'last_name' => 'Faculty',
            'email' => $email,
            'password' => Hash::make($password),
            'is_active' => true,
            'setup_completed_at' => now(),
        ]);
        DB::table('faculty_profiles')->insert(['user_id' => $user->id]);

        return $user;
    }
}
