<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        if ($user->is_active === false) {
            return response()->json(['message' => 'Your account has been deactivated.'], 403);
        }

        if (! $user->isStudent()) {
            return response()->json(['message' => 'Mobile access is for students only.'], 403);
        }

        $user->update(['last_login_at' => now()]);

        $token = ApiToken::generate($user->id);

        return response()->json([
            'token' => $token->token,
            'user'  => $this->userPayload($user),
        ]);
    }

    public function signup(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        // role_id 2 = student (matches Role::STUDENT constant in the app)
        $user = User::create([
            'role_id'    => 2,
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'password'   => $data['password'],
        ]);

        StudentProfile::create(['user_id' => $user->id]);

        $token = ApiToken::generate($user->id);

        return response()->json([
            'token' => $token->token,
            'user'  => $this->userPayload($user),
        ], 201);
    }

    public function logout(Request $request)
    {
        $raw = $request->bearerToken();
        if ($raw) {
            ApiToken::where('token', $raw)->delete();
        }

        return response()->json(['message' => 'Logged out.']);
    }

    public function user()
    {
        return response()->json(['user' => $this->userPayload(Auth::user())]);
    }

    private function userPayload(User $user): array
    {
        $profile = $user->studentProfile;

        return [
            'id'              => $user->id,
            'first_name'      => $user->first_name,
            'last_name'       => $user->last_name,
            'name'            => $user->name,
            'email'           => $user->email,
            'profile_photo'   => $user->profile_photo,
            'streak_days'     => (int) ($profile->streak_days ?? 0),
            'total_points'    => (int) ($profile->total_points ?? 0),
            'exam_target_date'=> $profile->exam_target_date ?? null,
        ];
    }
}
