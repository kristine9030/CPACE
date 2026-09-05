<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Covers MockExamController: the access-code unlock gate and the alumni-only
 * lockout, both of which are session-state, not DB rows. Hand-built schema,
 * same rationale as the other Feature tests in this suite.
 */
class MockExamTest extends TestCase
{
    private const TABLES = ['notifications', 'messages', 'conversation_participants', 'conversations', 'student_profiles', 'users'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['mockexam.access_code' => 'SECRET123']);

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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->boolean('is_alumni')->default(false);
        });
    }

    protected function tearDown(): void
    {
        foreach (self::TABLES as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    public function test_the_simulation_is_locked_until_the_correct_access_code_is_entered(): void
    {
        $student = $this->student();

        $this->actingAs($student)->get(route('mock-exams.simulation'))
            ->assertRedirect(route('mock-exams'));

        $this->actingAs($student)->post(route('mock-exams.unlock'), ['access_code' => 'wrong'])
            ->assertSessionHasErrors('access_code');

        $this->actingAs($student)->post(route('mock-exams.unlock'), ['access_code' => 'SECRET123'])
            ->assertRedirect(route('mock-exams'));

        $this->actingAs($student)->get(route('mock-exams.simulation'))->assertOk();
    }

    public function test_the_access_code_check_is_case_insensitive_and_trims_whitespace(): void
    {
        $student = $this->student();

        $this->actingAs($student)->post(route('mock-exams.unlock'), ['access_code' => '  secret123  '])
            ->assertSessionDoesntHaveErrors();
    }

    public function test_an_alumnus_cannot_unlock_or_enter_the_mock_exam(): void
    {
        $alumnusStudent = $this->student(isAlumni: true);

        $this->actingAs($alumnusStudent)->post(route('mock-exams.unlock'), ['access_code' => 'SECRET123'])
            ->assertForbidden();

        $this->actingAs($alumnusStudent)->get(route('mock-exams.simulation'))->assertForbidden();
    }

    private function student(bool $isAlumni = false): User
    {
        $student = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'setup_completed_at' => now(),
        ]);
        \DB::table('student_profiles')->insert(['user_id' => $student->id, 'is_alumni' => $isAlumni]);

        return $student;
    }
}
