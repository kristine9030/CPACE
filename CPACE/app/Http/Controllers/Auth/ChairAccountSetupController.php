<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChairAccountSetupController extends Controller
{
    /**
     * Show the first-login "change your password" prompt for a Program Chair
     * account the Super Admin just created.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        if (! $user->needsSetup()) {
            return redirect()->route('chair.dashboard');
        }

        return view('auth.chair-account-setup');
    }

    /**
     * Persist the new password and mark the account as set up.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        ]);

        $request->user()->update([
            'password' => Hash::make($data['password']),
            'setup_completed_at' => now(),
            'temp_password' => null,
        ]);

        return redirect()->route('chair.dashboard')->with('status', 'Your password has been updated.');
    }
}
