<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Alumni "About" card shown on their posts — batch year, job, links.
     */
    public function edit()
    {
        $user = Auth::user();

        $unreadNotifications = DB::table('notifications')
            ->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('alumni.profile', [
            'profile' => $user->alumniProfile,
            'unreadNotifications' => $unreadNotifications,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'batch_year'   => ['nullable', 'integer', 'digits:4', 'between:1980,2100'],
            'current_job'  => ['nullable', 'string', 'max:120'],
            'company'      => ['nullable', 'string', 'max:120'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'bio'          => ['nullable', 'string', 'max:1000'],
        ]);

        Auth::user()->alumniProfile()->updateOrCreate(
            ['user_id' => Auth::id()],
            $data
        );

        return back()->with('status', 'Profile updated.');
    }
}
