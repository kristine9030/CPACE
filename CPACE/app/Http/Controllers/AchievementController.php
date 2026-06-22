<?php

namespace App\Http\Controllers;

use App\Services\AchievementService;
use Illuminate\Support\Facades\Auth;

/**
 * Student achievements page.
 *
 * Every badge, the tier progress, the leaderboard and the student's rank are
 * computed live from the student's quiz history by AchievementService - nothing
 * on the page is hard-coded.
 */
class AchievementController extends Controller
{
    public function __construct(private AchievementService $achievements) {}

    public function index()
    {
        $user      = Auth::user();
        $studentId = $user->id;

        $data        = $this->achievements->build($studentId);
        $leaderboard = $this->achievements->leaderboards($studentId);

        return view('student.achievements', [
            'user'         => $user,
            'badges'       => $data['badges'],
            'categories'   => $data['categories'],
            'tierProgress' => $data['tier_progress'],
            'earnedCount'  => $data['earned_count'],
            'totalCount'   => $data['total_count'],
            'earnedMonth'  => $data['earned_this_month'],
            'activeDays'   => $data['active_days'],
            'streak'       => $data['streak'],
            'leaderboard'  => $leaderboard,
        ]);
    }
}
