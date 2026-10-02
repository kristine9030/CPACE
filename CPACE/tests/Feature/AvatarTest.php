<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Avatars: pictures from public/images/AVATARS. A user who hasn't chosen one
 * is given one by the system; choosing replaces it (and any uploaded photo).
 */
class AvatarTest extends TestCase
{
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
            $table->timestamp('setup_completed_at')->nullable();
            $table->string('temp_password')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('avatar', 80)->nullable();
            $table->string('avatar_color', 20)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');
        parent::tearDown();
    }

    public function test_the_avatar_folder_has_pictures_to_choose_from(): void
    {
        $this->assertNotEmpty(User::availableAvatars());
        foreach (User::availableAvatars() as $file) {
            $this->assertFileExists(public_path('images/AVATARS/' . $file));
        }
    }

    public function test_someone_who_never_customised_gets_a_stable_default_avatar(): void
    {
        $a = $this->faculty('a@example.com');
        $b = $this->faculty('b@example.com');

        $this->assertNull($a->avatar);
        $this->assertNotNull($a->avatarUrl());
        $this->assertStringContainsString('/images/AVATARS/', $a->avatarUrl());
        $this->assertSame($a->avatarUrl(), $a->fresh()->avatarUrl(), 'the default does not change between loads');
        $this->assertNotSame($a->avatarUrl(), $b->avatarUrl(), 'neighbouring accounts do not all look the same');
    }

    public function test_a_chosen_avatar_beats_the_default_and_an_uploaded_photo_beats_both(): void
    {
        $user = $this->faculty();
        $pick = User::availableAvatars()[3];
        $user->update(['avatar' => $pick]);
        $this->assertStringContainsString(rawurlencode($pick), $user->avatarUrl());

        $user->update(['profile_photo' => 'avatars/me.jpg']);
        $this->assertStringContainsString('storage/avatars/me.jpg', $user->avatarUrl());
        $this->assertStringContainsString(rawurlencode($pick), $user->presetAvatarUrl());
    }

    public function test_faculty_can_pick_an_avatar_from_the_profile_modal(): void
    {
        $faculty = $this->faculty();
        $pick = User::availableAvatars()[1];

        $this->actingAs($faculty)->post(route('faculty.settings.profile'), [
            'first_name' => $faculty->first_name, 'last_name' => $faculty->last_name, 'email' => $faculty->email,
            'avatar' => $pick, 'remove_photo' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame($pick, $faculty->fresh()->avatar);
    }

    public function test_only_avatars_from_the_folder_are_accepted(): void
    {
        $faculty = $this->faculty();

        $this->actingAs($faculty)->post(route('faculty.settings.profile'), [
            'first_name' => $faculty->first_name, 'last_name' => $faculty->last_name, 'email' => $faculty->email,
            'avatar' => '../../.env',
        ])->assertSessionHasErrors('avatar');

        $this->assertNull($faculty->fresh()->avatar);
    }

    public function test_saving_the_profile_without_touching_the_avatar_keeps_it_unset(): void
    {
        $faculty = $this->faculty();

        $this->actingAs($faculty)->post(route('faculty.settings.profile'), [
            'first_name' => 'New', 'last_name' => 'Name', 'email' => $faculty->email, 'avatar' => '',
        ])->assertRedirect();

        $this->assertNull($faculty->fresh()->avatar);
        $this->assertNotNull($faculty->fresh()->avatarUrl());
    }

    public function test_chair_and_super_admin_can_edit_their_profile_and_pick_an_avatar(): void
    {
        foreach ([Role::ADMIN, Role::SUPER_ADMIN] as $i => $role) {
            $user = User::create([
                'role_id' => $role, 'first_name' => 'Pat', 'last_name' => 'User' . $i,
                'email' => "pat{$i}@example.com", 'password' => Hash::make('password'), 'is_active' => true, 'setup_completed_at' => now(),
            ]);
            $pick = User::availableAvatars()[$i];

            $this->actingAs($user)->post(route('profile.update'), [
                'first_name' => 'Renamed', 'last_name' => $user->last_name, 'email' => $user->email, 'avatar' => $pick,
            ])->assertRedirect()->assertSessionHasNoErrors();

            $fresh = $user->fresh();
            $this->assertSame('Renamed', $fresh->first_name);
            $this->assertSame($pick, $fresh->avatar);
        }
    }

    public function test_profile_update_needs_a_signed_in_user_and_a_real_avatar(): void
    {
        $this->post(route('profile.update'), ['first_name' => 'X', 'last_name' => 'Y', 'email' => 'x@example.com'])->assertRedirect(route('login'));

        $user = $this->faculty();
        $this->actingAs($user)->post(route('profile.update'), [
            'first_name' => 'X', 'last_name' => 'Y', 'email' => $user->email, 'avatar' => 'not-in-the-folder.png',
        ])->assertSessionHasErrors('avatar');
    }

    private function faculty(string $email = 'fac@example.com'): User
    {
        return User::create([
            'role_id' => Role::FACULTY, 'first_name' => 'Fay', 'last_name' => 'Culty',
            'email' => $email, 'password' => Hash::make('password'), 'is_active' => true, 'setup_completed_at' => now(),
        ]);
    }
}
