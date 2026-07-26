<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FacultySettingsController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        return view('faculty.settings', [
            'profile' => $user->facultyProfile,
        ]);
    }

    /**
     * Quick profile edit (name, email, avatar) — used by both the topbar/sidebar
     * profile modal and the Account card on the full settings page.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'photo'      => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $data['profile_photo'] = $request->file('photo')->store('avatars', 'public');
        }
        unset($data['photo']);

        $user->update($data);

        return back()->with('status', 'Profile updated successfully.');
    }

    /**
     * Faculty-specific details (employee number, department) on the full settings page.
     */
    public function updateDetails(Request $request)
    {
        $data = $request->validate([
            'employee_number' => ['nullable', 'string', 'max:20', Rule::unique('faculty_profiles', 'employee_number')->ignore(Auth::id(), 'user_id')],
            'department'      => ['nullable', 'string', 'max:100'],
        ]);

        Auth::user()->facultyProfile()->updateOrCreate(
            ['user_id' => Auth::id()],
            $data
        );

        return back()->with('status', 'Details updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password'      => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return back()->with('status', 'Password changed successfully.');
    }
}
