<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * "Profile Settings" for roles that have no settings page of their own
 * (Program Chair, Super Admin, Alumni): name, email, photo and avatar.
 * Students and faculty keep their own settings controllers.
 */
class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'first_name'   => ['required', 'string', 'max:100'],
            'last_name'    => ['required', 'string', 'max:100'],
            'email'        => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'photo'        => ['nullable', 'image', 'max:2048'],
            'avatar'       => ['nullable', 'string', Rule::in(User::availableAvatars())],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $data['profile_photo'] = $request->file('photo')->store('avatars', 'public');
        } elseif ($request->boolean('remove_photo') && $user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
            $data['profile_photo'] = null;
        }
        unset($data['photo'], $data['remove_photo']);

        $user->update($data);

        return back()->with('status', 'Profile updated successfully.');
    }
}
