<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Concerns\GeneratesOneTimePassword;
use App\Http\Controllers\Controller;

use App\Mail\AccountCredentialsMail;
use App\Models\Role;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Services\WeaknessDetector;
use App\Services\ChairAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ProgramChairController extends Controller
{
    use GeneratesOneTimePassword;

    private const INACTIVITY_DAYS = 7;

    /**
     * Program Chair overview: faculty count, subject coverage, assignments.
     */
    public function dashboard(ChairAnalyticsService $analytics)
    {
        $subjects = Subject::withCount('faculty')->orderBy('id')->get();
        $atRiskStudents = $this->atRiskStudents();

        $stats = [
            'faculty'    => User::where('role_id', Role::FACULTY)->count(),
            'subjects'   => $subjects->count(),
            'assigned'   => DB::table('faculty_subjects')->distinct('subject_id')->count('subject_id'),
            'unassigned' => $subjects->where('faculty_count', 0)->count(),
        ];

        return view('chair.dashboard', [
            'stats'    => $stats,
            'subjects' => $subjects,
            'faculty'  => User::where('role_id', Role::FACULTY)
                ->with('assignedSubjects')
                ->orderByDesc('id')
                ->take(5)
                ->get(),
            'atRiskStudents' => $atRiskStudents,
            'recommendedActions' => $analytics->recommendedActions($atRiskStudents),
            'analytics' => $analytics->dashboardSummary(),
        ]);
    }

    /**
     * System-wide intervention list. The readiness rule matches the faculty
     * performance page; inactivity is based on the latest quiz/login activity.
     */
    private function atRiskStudents()
    {
        $quizActivity = DB::table('quiz_sessions')
            ->where('session_type', '!=', 'training')->where('is_practice_room', false)
            ->whereNotNull('completed_at')
            ->groupBy('student_id')
            ->select(
                'student_id',
                DB::raw('COALESCE(SUM(total_items), 0) as attempted'),
                DB::raw('COALESCE(SUM(correct_answers), 0) as correct'),
                DB::raw('COUNT(*) as quizzes'),
                DB::raw('MAX(completed_at) as last_quiz_at')
            )
            ->get()
            ->keyBy('student_id');

        return User::where('role_id', Role::STUDENT)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get()
            ->map(function (User $student) use ($quizActivity) {
                $activity = $quizActivity->get($student->id);
                $attempted = (int) ($activity->attempted ?? 0);
                $correct = (int) ($activity->correct ?? 0);
                $score = $attempted > 0 ? (int) round($correct / $attempted * 100) : null;

                $dates = collect([
                    $activity?->last_quiz_at,
                    $student->last_login_at,
                    $student->created_at,
                ])->filter()->map(fn ($date) => Carbon::parse($date));

                $lastActive = $dates->sortDesc()->first();
                $daysIdle = $lastActive ? (int) $lastActive->diffInDays(now()) : self::INACTIVITY_DAYS;
                $lowReadiness = $attempted >= WeaknessDetector::MIN_ATTEMPTS
                    && $score < (int) (WeaknessDetector::ACCURACY_THRESHOLD * 100);
                $inactive = $daysIdle >= self::INACTIVITY_DAYS;

                if (! $lowReadiness && ! $inactive) {
                    return null;
                }

                $reasons = [];
                if ($lowReadiness) {
                    $reasons[] = 'Low readiness';
                }
                if ($inactive) {
                    $reasons[] = $attempted === 0 ? 'No learning activity' : 'Inactive';
                }

                $name = $student->name;
                $initials = strtoupper(substr($student->first_name, 0, 1).substr($student->last_name, 0, 1));
                $isHigh = ($lowReadiness && $inactive) || ($score !== null && $score < 45) || $daysIdle >= 14;

                return [
                    'id'          => $student->id,
                    'name'        => $name,
                    'email'       => $student->email,
                    'initials'    => $initials,
                    'score'       => $score,
                    'attempted'   => $attempted,
                    'quizzes'     => (int) ($activity->quizzes ?? 0),
                    'last_active' => $lastActive,
                    'days_idle'   => $daysIdle,
                    'reasons'     => $reasons,
                    'priority'    => $isHigh ? 'high' : 'watch',
                ];
            })
            ->filter()
            ->sort(function (array $a, array $b) {
                $aRank = [$a['priority'] === 'high' ? 0 : 1, $a['score'] ?? -1, -$a['days_idle']];
                $bRank = [$b['priority'] === 'high' ? 0 : 1, $b['score'] ?? -1, -$b['days_idle']];

                return $aRank <=> $bRank;
            })
            ->values();
    }

    /**
     * Faculty account management with their assigned subjects.
     */
    public function faculty()
    {
        $faculty = User::where('role_id', Role::FACULTY)
            ->with('assignedSubjects', 'assignedSections')
            ->orderBy('first_name')
            ->get();

        $faculty->each(function (User $f) {
            $f->sectionsBySubject = $f->assignedSections
                ->groupBy(fn ($s) => $s->pivot->subject_id)
                ->map(fn ($group) => $group->pluck('id'));
        });

        return view('chair.faculty', [
            'faculty'  => $faculty,
            'subjects' => Subject::orderBy('id')->get(),
            'sections' => Section::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the "create faculty account" form.
     */
    public function createFaculty()
    {
        return view('chair.faculty-form', [
            'editMode' => false,
            'subjects' => Subject::orderBy('id')->get(),
            'sections' => Section::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Create a faculty login and assign subjects (optionally scoped to sections) in one step.
     */
    public function storeFaculty(Request $request)
    {
        $data = $request->validate([
            'first_name'      => 'required|string|max:60',
            'last_name'       => 'required|string|max:60',
            'email'           => 'required|email|max:120|unique:users,email',
            'employee_number' => 'nullable|string|max:20',
            'department'      => 'nullable|string|max:100',
            'subjects'        => 'array',
            'subjects.*'      => 'integer|exists:subjects,id',
            'sections'        => 'array',
            'sections.*'      => 'array',
            'sections.*.*'    => 'integer|exists:sections,id',
        ]);

        $tempPassword = $this->generateOneTimePassword();

        $user = DB::transaction(function () use ($data, $request, $tempPassword) {
            $user = User::create([
                'role_id'            => Role::FACULTY,
                'first_name'         => $data['first_name'],
                'last_name'          => $data['last_name'],
                'email'              => $data['email'],
                'password'           => Hash::make($tempPassword),
                'is_active'          => true,
                'email_verified'     => true,
                'setup_completed_at' => null,
                'temp_password'      => $tempPassword,
            ]);

            $user->facultyProfile()->create([
                'employee_number' => $data['employee_number'] ?? null,
                'department'      => $data['department'] ?? 'College of Accountancy',
            ]);

            $this->syncSubjects($user, $request->input('subjects', []), $request->input('sections', []));

            return $user;
        });

        $mailed = $this->mailCredentials($user, $tempPassword, 'faculty');

        return redirect()->route('chair.faculty')->with(
            'status',
            $mailed
                ? "Faculty account created. The one-time password was emailed to {$user->email}."
                : "Faculty account created, but the credentials email couldn't be sent. Use \"Resend OTP\" on this account's row to try again."
        );
    }

    /**
     * Show the edit form for an existing faculty account.
     */
    public function editFaculty(int $id)
    {
        $faculty = User::where('role_id', Role::FACULTY)
            ->with(['assignedSubjects', 'assignedSections', 'facultyProfile'])
            ->findOrFail($id);

        return view('chair.faculty-form', [
            'editMode' => true,
            'faculty'  => $faculty,
            'subjects' => Subject::orderBy('id')->get(),
            'sections' => Section::where('is_active', true)->orderBy('name')->get(),
            'assigned' => $faculty->assignedSubjects->pluck('id')->all(),
            'assignedSections' => $faculty->assignedSections
                ->groupBy(fn ($s) => $s->pivot->subject_id)
                ->map(fn ($group) => $group->pluck('id')->all())
                ->all(),
        ]);
    }

    /**
     * Update a faculty account details, password (optional) and assignments.
     */
    public function updateFaculty(Request $request, int $id)
    {
        $faculty = User::where('role_id', Role::FACULTY)->findOrFail($id);

        $data = $request->validate([
            'first_name'      => 'required|string|max:60',
            'last_name'       => 'required|string|max:60',
            'email'           => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($faculty->id)],
            'employee_number' => 'nullable|string|max:20',
            'department'      => 'nullable|string|max:100',
            'is_active'       => 'nullable|boolean',
            'subjects'        => 'array',
            'subjects.*'      => 'integer|exists:subjects,id',
            'sections'        => 'array',
            'sections.*'      => 'array',
            'sections.*.*'    => 'integer|exists:sections,id',
        ]);

        DB::transaction(function () use ($faculty, $data, $request) {
            $faculty->update([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'is_active'  => $request->boolean('is_active'),
            ]);

            $faculty->facultyProfile()->updateOrCreate(
                ['user_id' => $faculty->id],
                [
                    'employee_number' => $data['employee_number'] ?? null,
                    'department'      => $data['department'] ?? null,
                ]
            );

            $this->syncSubjects($faculty, $request->input('subjects', []), $request->input('sections', []));
        });

        return redirect()->route('chair.faculty')->with('status', 'Faculty account updated.');
    }

    /**
     * Quick subject (re)assignment from the faculty list.
     */
    public function assignSubjects(Request $request, int $id)
    {
        $faculty = User::where('role_id', Role::FACULTY)->findOrFail($id);

        $request->validate([
            'subjects'     => 'array',
            'subjects.*'   => 'integer|exists:subjects,id',
            'sections'     => 'array',
            'sections.*'   => 'array',
            'sections.*.*' => 'integer|exists:sections,id',
        ]);

        $this->syncSubjects($faculty, $request->input('subjects', []), $request->input('sections', []));

        return redirect()->route('chair.faculty')
            ->with('status', "Subjects updated for {$faculty->name}.");
    }

    /**
     * Subject-centric view: which faculty handle each CPALE subject.
     */
    public function subjects()
    {
        return view('chair.subjects', [
            'subjects' => Subject::with('faculty')->orderBy('id')->get(),
            'faculty'  => User::where('role_id', Role::FACULTY)->orderBy('first_name')->get(),
        ]);
    }

    /**
     * Activate / deactivate a faculty login without deleting their data.
     */
    public function toggleFaculty(int $id)
    {
        $faculty = User::where('role_id', Role::FACULTY)->findOrFail($id);
        $faculty->update(['is_active' => !$faculty->is_active]);

        return back()->with('status', $faculty->is_active
            ? "{$faculty->name}'s account is now active."
            : "{$faculty->name}'s account has been deactivated.");
    }

    /**
     * Issue a fresh one-time password for a faculty account still pending
     * first-login setup. Covers accounts created before OTPs were persisted
     * (only the bcrypt hash was kept, so the original can't be recovered) as
     * well as a faculty member who simply lost their original OTP.
     */
    public function regenerateFacultyOtp(int $id)
    {
        $faculty = User::where('role_id', Role::FACULTY)->findOrFail($id);

        if ($faculty->setup_completed_at !== null) {
            return back()->with('error', 'This account has already completed setup.');
        }

        $tempPassword = $this->generateOneTimePassword();
        $faculty->update([
            'password' => Hash::make($tempPassword),
            'temp_password' => $tempPassword,
        ]);

        $mailed = $this->mailCredentials($faculty, $tempPassword, 'faculty', reissue: true);

        return back()->with(
            'status',
            $mailed
                ? "A new one-time password was emailed to {$faculty->name} ({$faculty->email})."
                : "A new one-time password was generated for {$faculty->name}, but the email couldn't be sent. Try \"Resend OTP\" again."
        );
    }

    /**
     * Emails the OTP straight to the account owner's inbox — the Program
     * Chair never sees it. Failures are swallowed (and logged) so a mail
     * outage doesn't block account creation.
     */
    private function mailCredentials(User $user, string $tempPassword, string $roleLabel, bool $reissue = false): bool
    {
        try {
            Mail::to($user->email)->send(new AccountCredentialsMail($user, $tempPassword, $roleLabel, $reissue));

            return true;
        } catch (\Throwable $exception) {
            Log::error('Failed to email account credentials.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Attach pivot rows with the chair who made the assignment + timestamp,
     * and (fully) replace this faculty's section restrictions to match
     * exactly what was submitted. A subject with no section ids here leaves
     * the faculty unrestricted for it (sees the whole subject).
     */
    private function syncSubjects(User $faculty, array $subjectIds, array $sectionsBySubject = []): void
    {
        $pivot = [];
        foreach ($subjectIds as $sid) {
            $pivot[(int) $sid] = [
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
            ];
        }

        $faculty->assignedSubjects()->sync($pivot);

        DB::table('faculty_subject_sections')->where('faculty_id', $faculty->id)->delete();

        $rows = [];
        foreach ($subjectIds as $sid) {
            foreach ($sectionsBySubject[$sid] ?? [] as $secId) {
                $rows[] = [
                    'faculty_id'  => $faculty->id,
                    'subject_id'  => (int) $sid,
                    'section_id'  => (int) $secId,
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        if ($rows !== []) {
            DB::table('faculty_subject_sections')->insert($rows);
        }
    }
}
