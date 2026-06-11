<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->forceFill(['last_login_at' => now()])->save();

            return redirect()->intended($this->homeFor($user));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show the signup form
     */
    public function showSignup()
    {
        return view('auth.signup');
    }

    /**
     * Handle signup request
     */
    public function signup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // The schema stores first and last name separately.
        $name = trim($validated['name']);
        $firstName = strtok($name, ' ');
        $lastName = trim(substr($name, strlen($firstName)));

        $user = User::create([
            'role_id' => Role::STUDENT,
            'first_name' => $firstName,
            'last_name' => $lastName !== '' ? $lastName : $firstName,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended($this->homeFor($user));
    }

    /**
     * Where to send a user after authenticating, based on their role.
     */
    protected function homeFor(User $user): string
    {
        return $user->isFaculty()
            ? route('faculty.dashboard')
            : route('dashboard');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
