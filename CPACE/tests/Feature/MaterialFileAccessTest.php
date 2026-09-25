<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uploaded materials must only be reachable through the login-checked route:
 * no public storage URL, no download route, drafts hidden from students.
 */
class MaterialFileAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
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
        // Student requests pass through middleware that reads these.
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
        });
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id')->default(1);
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('title');
            $table->string('kind', 10)->default('file');
            $table->string('file_category', 20)->default('other');
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('external_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        foreach (['materials', 'student_profiles', 'notifications', 'messages', 'conversation_participants', 'conversations', 'users'] as $table) {
            Schema::dropIfExists($table);
        }
        parent::tearDown();
    }

    private function user(int $roleId): User
    {
        static $n = 0;
        $n++;

        return User::forceCreate([
            'role_id' => $roleId, 'first_name' => 'T', 'last_name' => 'U', 'email' => "u{$n}@t.test",
            'password' => 'x', 'setup_completed_at' => now(),
        ]);
    }

    private function material(bool $active = true, string $disk = 'local'): Material
    {
        Storage::disk($disk)->put('materials/a.pdf', 'PDFDATA');

        return Material::forceCreate([
            'title' => 'Notes', 'kind' => 'file', 'file_category' => 'pdf',
            'file_path' => 'materials/a.pdf', 'original_name' => 'a.pdf', 'is_active' => $active,
        ]);
    }

    public function test_the_download_route_no_longer_exists(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('materials.download'));
    }

    public function test_a_material_url_is_the_guarded_route_not_a_storage_url(): void
    {
        $m = $this->material();

        $this->assertSame(route('materials.file', $m->id), $m->url());
        $this->assertStringNotContainsString('/storage/', $m->url());
    }

    public function test_guests_cannot_open_the_file(): void
    {
        $m = $this->material();

        $this->get(route('materials.file', $m->id))->assertForbidden();
    }

    public function test_a_signed_in_student_can_view_it_inline_and_it_is_not_an_attachment(): void
    {
        $m = $this->material();

        $response = $this->actingAs($this->user(2))->get(route('materials.file', $m->id));

        $response->assertOk();
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_a_student_cannot_open_a_draft_material(): void
    {
        $m = $this->material(active: false);

        $this->actingAs($this->user(2))->get(route('materials.file', $m->id))->assertNotFound();
    }

    public function test_a_valid_signed_link_works_without_a_session_and_a_tampered_one_does_not(): void
    {
        $m = $this->material();
        $signed = $m->previewUrl();

        $this->get($signed)->assertOk();
        $this->get($signed . 'x')->assertForbidden();
    }

    public function test_legacy_files_on_the_public_disk_are_still_served_until_migrated(): void
    {
        $m = $this->material(disk: 'public');

        $this->actingAs($this->user(2))->get(route('materials.file', $m->id))->assertOk();
    }

    public function test_the_secure_command_moves_public_files_to_private_storage(): void
    {
        $this->material(disk: 'public');

        $this->artisan('materials:secure')->assertSuccessful();

        Storage::disk('local')->assertExists('materials/a.pdf');
        Storage::disk('public')->assertMissing('materials/a.pdf');
    }
}
