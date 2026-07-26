<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Mail\ResetPasswordMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Laravel\Socialite\Facades\Socialite;

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

            if ($user->isShifted()) {
                return $this->rejectShiftedLogin($request, $user);
            }

            $user->forceFill(['last_login_at' => now()])->save();

            return redirect()->intended($this->homeFor($user));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * A student the Program Chair marked as "shifted" out of the program is
     * logged straight back out, rather than getting a session. The specific
     * reason is not shown here (data privacy) — it's only visible to the
     * Program Chair in the student's record.
     */
    protected function rejectShiftedLogin(Request $request, User $user)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()->withErrors([
            'email' => 'This account has shifted out of the program and no longer has access to CPACE. Please contact the Program Chair for more information.',
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
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
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
        if ($user->isChair()) {
            return route('chair.dashboard');
        }

        if ($user->isFaculty()) {
            return route('faculty.dashboard');
        }

        if ($user->isAlumni()) {
            return route('community.index');
        }

        return route('dashboard');
    }

    // ── Social OAuth ─────────────────────────────────────────────────────────

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $socialUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Google login failed. Please try again.']);
        }

        return $this->handleSocialLogin($socialUser, 'Google');
    }

    public function redirectToMicrosoft()
    {
        return Socialite::driver('azure')->redirect();
    }

    public function handleMicrosoftCallback()
    {
        try {
            $socialUser = Socialite::driver('azure')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Microsoft login failed. Please try again.']);
        }

        return $this->handleSocialLogin($socialUser, 'Microsoft');
    }

    protected function handleSocialLogin($socialUser, string $provider)
    {
        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            return redirect()->route('login')->withErrors([
                'email' => "No account found with that {$provider} email. Please contact your school administrator.",
            ]);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        if ($user->isShifted()) {
            return $this->rejectShiftedLogin(request(), $user);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended($this->homeFor($user));
    }

    /**
     * Show the forgot password form
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle forgot password submission: email a signed reset link if the
     * account exists. The response message is the same either way so an
     * attacker can't use it to discover which emails are registered.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Password::createToken($user);
            $resetUrl = route('password.reset', ['token' => $token]) . '?email=' . urlencode($user->email);

            Mail::to($user->email)->send(new ResetPasswordMail($user, $resetUrl));
        }

        return back()->with('status', 'If an account with that email exists, we\'ve sent a password reset link to it. Please check your inbox.');
    }

    /**
     * Show the "set a new password" form linked from the reset email.
     */
    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Handle the new-password submission from the reset form.
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Your password has been reset. You can now log in.');
        }

        return back()->withErrors(['email' => __($status)])->onlyInput('email');
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
