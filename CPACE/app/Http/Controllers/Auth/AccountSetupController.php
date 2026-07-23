<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountSetupController extends Controller
{
    /** CPALE subject codes offered as focus areas. */
    private const SUBJECTS = ['FAR', 'AFAR', 'MS', 'TAX', 'AUD', 'RFBT'];

    private const DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    private const TIMES = ['Morning', 'Afternoon', 'Evening', 'Late night'];

    private const INTENSITIES = ['Light', 'Steady', 'Intense'];

    /**
     * Show the first-login onboarding wizard, prefilled with anything the
     * chair already imported for this student.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Students who already finished setup don't need this page again.
        if (! $user->needsSetup() && $user->setup_completed_at !== null) {
            return redirect()->route('dashboard');
        }

        return view('auth.account-setup', [
            'profile' => $user->studentProfile,
        ]);
    }

    /**
     * Persist the new password, personal details, and study plan, then mark
     * the account as set up and send the student to the welcome screen.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'password'        => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            'student_number'  => ['nullable', 'string', 'max:30', Rule::unique('student_profiles', 'student_number')->ignore($user->id, 'user_id')],
            'section'         => ['nullable', 'string', 'max:30'],
            'year_level'      => ['nullable', 'integer', 'between:1,6'],
            'mobile'          => ['nullable', 'string', 'max:30'],
            'exam_month'      => ['nullable', 'integer', 'between:1,12'],
            'exam_year'       => ['nullable', 'integer', 'between:2026,2035'],
            'study_days'      => ['nullable', 'string', 'max:60'],
            'study_time'      => ['nullable', 'string', Rule::in(self::TIMES)],
            'study_intensity' => ['nullable', 'string', Rule::in(self::INTENSITIES)],
            'focus_subjects'  => ['nullable', 'string', 'max:120'],
        ]);

        // Build the target exam date from the month + year pickers (day 15).
        $examDate = null;
        if (! empty($data['exam_month']) && ! empty($data['exam_year'])) {
            $examDate = Carbon::create((int) $data['exam_year'], (int) $data['exam_month'], 15)->toDateString();
        }

        // Keep only recognized day/subject tokens.
        $days = $this->clean($data['study_days'] ?? '', self::DAYS);
        $subjects = $this->clean($data['focus_subjects'] ?? '', self::SUBJECTS);

        $user->update([
            'password' => Hash::make($data['password']),
            'setup_completed_at' => now(),
        ]);

        StudentProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'student_number'  => $data['student_number'] ?? $user->studentProfile?->student_number,
                'section'         => $data['section'] ?? null,
                'year_level'      => $data['year_level'] ?? null,
                'mobile'          => $data['mobile'] ?? null,
                'exam_target_date' => $examDate,
                'study_days'      => $days ?: null,
                'study_time'      => $data['study_time'] ?? null,
                'study_intensity' => $data['study_intensity'] ?? null,
                'focus_subjects'  => $subjects ?: null,
            ]
        );

        return redirect()->route('onboarding.welcome');
    }

    /**
     * Celebratory landing shown right after setup, summarizing the plan.
     */
    public function welcome(Request $request)
    {
        $profile = $request->user()->studentProfile;

        $examTarget = $profile?->exam_target_date
            ? $profile->exam_target_date->format('F Y')
            : 'Not set';

        return view('auth.onboarding-welcome', [
            'examTarget' => $examTarget,
            'intensity'  => $profile?->study_intensity ?: 'Steady',
            'studyDays'  => $profile?->study_days ?: 'Not set',
            'focus'      => $profile?->focus_subjects ?: 'Not set',
        ]);
    }

    /**
     * Filter a comma-separated list down to an allow-list, preserving order.
     */
    private function clean(string $csv, array $allowed): string
    {
        $tokens = array_filter(array_map('trim', explode(',', $csv)));
        $kept = array_values(array_intersect($tokens, $allowed));

        return implode(',', $kept);
    }
}
