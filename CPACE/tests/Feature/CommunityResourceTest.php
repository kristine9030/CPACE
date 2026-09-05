<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Covers CommunityResourceController (the "Resource Library"): who may
 * upload/delete, and that a download increments the counter. Hand-built
 * schema, same rationale as the other Feature tests in this suite.
 */
class CommunityResourceTest extends TestCase
{
    private const TABLES = [
        'community_resources', 'notifications', 'messages',
        'conversation_participants', 'conversations', 'student_profiles', 'users',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

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
        Schema::create('community_resources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uploader_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('file_category', 20)->default('other');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('downloads_count')->default(0);
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

    public function test_an_alumnus_can_upload_a_resource(): void
    {
        $alumnus = $this->user(Role::ALUMNI, 'alumnus@example.com');

        $this->actingAs($alumnus)->post(route('community.resources.store'), [
            'title' => 'CPA Reviewer Notes',
            'file' => UploadedFile::fake()->create('notes.pdf', 500, 'application/pdf'),
        ])->assertRedirect();

        $this->assertSame(1, DB::table('community_resources')->where('uploader_id', $alumnus->id)->count());
    }

    public function test_a_student_cannot_upload_a_resource(): void
    {
        $student = $this->user(Role::STUDENT, 'student@example.com');

        $this->actingAs($student)->post(route('community.resources.store'), [
            'title' => 'Should Not Save',
            'file' => UploadedFile::fake()->create('notes.pdf', 500, 'application/pdf'),
        ])->assertForbidden();

        $this->assertSame(0, DB::table('community_resources')->count());
    }

    public function test_downloading_a_resource_increments_its_download_count(): void
    {
        $alumnus = $this->user(Role::ALUMNI, 'alumnus@example.com');
        $resourceId = $this->resource($alumnus->id);
        $student = $this->user(Role::STUDENT, 'student@example.com');

        $this->actingAs($student)->get(route('community.resources.download', $resourceId))->assertOk();

        $this->assertSame(1, DB::table('community_resources')->find($resourceId)->downloads_count);
    }

    public function test_a_resource_can_be_deleted_by_its_uploader_or_the_chair_but_not_by_anyone_else(): void
    {
        $alumnus = $this->user(Role::ALUMNI, 'uploader@example.com');
        $resourceId = $this->resource($alumnus->id);
        $bystander = $this->user(Role::STUDENT, 'bystander@example.com');

        $this->actingAs($bystander)->delete(route('community.resources.destroy', $resourceId))->assertForbidden();
        $this->assertNotNull(DB::table('community_resources')->find($resourceId));

        $chair = $this->user(Role::ADMIN, 'chair@example.com');
        $this->actingAs($chair)->delete(route('community.resources.destroy', $resourceId))->assertRedirect();
        $this->assertNull(DB::table('community_resources')->find($resourceId));
    }

    private function user(int $roleId, string $email): User
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

    private function resource(int $uploaderId): int
    {
        $path = UploadedFile::fake()->create('notes.pdf', 500, 'application/pdf')->store('community-resources', 'public');

        return DB::table('community_resources')->insertGetId([
            'uploader_id' => $uploaderId,
            'title' => 'A Resource',
            'file_path' => $path,
            'original_name' => 'notes.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
