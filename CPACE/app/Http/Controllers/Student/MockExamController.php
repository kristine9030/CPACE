<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MockExamController extends Controller
{
    /** Session key that marks the mock exam as unlocked for this login. */
    private const UNLOCK_KEY = 'mock_exam_unlocked';

    /**
     * Mock exams stay locked until faculty starts the session and hands out the
     * access code, so the list is shown behind a lock screen until then.
     */
    public function index(Request $request)
    {
        return view('student.mock-exams', [
            'mockUnlocked' => (bool) $request->session()->get(self::UNLOCK_KEY, false),
            'isAlumniLocked' => $request->user()->hasAlumniAccess(),
        ]);
    }

    /**
     * Validate the faculty-provided access code and, if correct, unlock the
     * mock exam for the rest of this session.
     */
    public function unlock(Request $request)
    {
        abort_if($request->user()->hasAlumniAccess(), 403, 'Mock exams are locked for alumni accounts.');

        $data = $request->validate([
            'access_code' => ['required', 'string', 'max:60'],
        ]);

        if (strcasecmp(trim($data['access_code']), (string) config('mockexam.access_code')) !== 0) {
            return back()->withErrors([
                'access_code' => 'Invalid access code. Please double-check the code your faculty gave you.',
            ]);
        }

        $request->session()->put(self::UNLOCK_KEY, true);

        return redirect()->route('mock-exams')->with('status', 'Mock exam unlocked — good luck!');
    }

    /**
     * The exam simulation itself is also gated: no valid unlock, no entry.
     */
    public function simulation(Request $request)
    {
        abort_if($request->user()->hasAlumniAccess(), 403, 'Mock exams are locked for alumni accounts.');

        if (! $request->session()->get(self::UNLOCK_KEY, false)) {
            return redirect()->route('mock-exams')
                ->withErrors(['access_code' => 'Enter the access code from your faculty to start the mock exam.']);
        }

        return view('student.mock-exam-simulation');
    }
}
