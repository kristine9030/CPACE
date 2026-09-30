<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Concerns\GeneratesOneTimePassword;
use App\Http\Controllers\Concerns\ReadsChartFilters;
use App\Http\Controllers\Controller;

use App\Mail\AccountCredentialsMail;
use App\Models\Role;
use App\Models\Section;
use App\Models\Subject;
use App\Models\User;
use App\Services\ChairAnalyticsService;
use App\Services\ChairDashboardService;
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
    use ReadsChartFilters;


    /** How far back the top KPI cards look to show a "vs 30 days ago" comparison. */
    private const KPI_COMPARISON_DAYS = 30;

    /** A student counts as "practising" with a completed quiz in this many days. */
    private const PRACTISE_WINDOW_DAYS = 7;

    /**
     * Program Chair overview: faculty count, subject coverage, assignments.
     */
    public function dashboard(Request $request, ChairAnalyticsService $analytics, ChairDashboardService $dashboard)
    {
        $subjects = Subject::withCount('faculty')->orderBy('id')->get();
        $atRiskStudents = $dashboard->atRiskStudents();
        $facultyWorkload = $analytics->facultyWorkload();
        $activeStudents = User::where('role_id', Role::STUDENT)->where('is_active', true)->pluck('id');

        // Headline row: the four numbers a chair should act on first. Each
        // opens the tab that holds its detail.
        $stats = [
            'students'           => $activeStudents->count(),
            'practising'         => $this->studentsPractising($activeStudents, now()->subDays(self::PRACTISE_WINDOW_DAYS)),
            'at_risk'            => $atRiskStudents->count(),
            'at_risk_high'       => $atRiskStudents->where('priority', 'high')->count(),
            'at_risk_inactive'   => $atRiskStudents->filter(fn ($s) => in_array('No learning activity', $s['reasons'], true))->count(),
            'faculty'            => $facultyWorkload->count(),
            'faculty_attention'  => $facultyWorkload->where('flag', '!=', 'ok')->count(),
            'faculty_idle'       => $facultyWorkload->where('flag', 'idle')->count(),
            'faculty_overloaded' => $facultyWorkload->where('flag', 'overloaded')->count(),
            'faculty_unassigned' => $facultyWorkload->where('flag', 'unassigned')->count(),
            'subjects'           => $subjects->count(),
            'assigned'           => DB::table('faculty_subjects')->distinct('subject_id')->count('subject_id'),
            'uncovered'          => $subjects->where('faculty_count', 0)->pluck('code'),
        ];

        return view('chair.dashboard', [
            'stats'    => $stats,
            'kpiDeltas' => $this->kpiComparisons($stats, $activeStudents),
            'subjects' => $subjects,
            'faculty'  => User::where('role_id', Role::FACULTY)
                ->with('assignedSubjects')
                ->orderByDesc('id')
                ->take(5)
                ->get(),
            'atRiskStudents' => $atRiskStudents,
            'recommendedActions' => $analytics->recommendedActions($atRiskStudents),
            // Only the Performance tab's cohort table still reads this; the
            // rest of the old summary is superseded by the filtered charts.
            'analytics' => $this->cohortBreakdown($analytics),
            'facultyWorkload' => $facultyWorkload,
            // When each inactive faculty member was last reminded (Faculty tab).
            'facultyReminders' => FacultyReminderController::lastReminderAt($facultyWorkload->where('flag', 'idle')->pluck('id')),
            'chartFilters' => $filters = $this->chartFilters($request),
            'chartDefaults' => $this->chartFilters(new Request()),
            // Overview is the tab most visits land on; the other tabs fetch
            // their own data the first time they are opened.
            'charts' => $this->charts($dashboard, $filters, ['overview']),
            'sectionOptions' => Section::where('is_active', true)->orderBy('year_level')->orderBy('name')->get(['name', 'year_level']),
        ]);
    }

    /**
     * JSON for the Overview tab's chart cards — called whenever the chair
     * changes the date range, subject or section filter.
     */
    public function dashboardData(Request $request, ChairDashboardService $dashboard)
    {
        $filters = $this->chartFilters($request);
        // One tab at a time: each tab keeps its own filters on the page.
        $tab = in_array($request->query('tab'), ChairDashboardService::TABS, true) ? $request->query('tab') : null;

        return response()->json([
            'filters' => $filters,
            'charts' => $this->charts($dashboard, $filters, $tab ? [$tab] : null),
        ]);
    }

    /** Per-year and per-section rows for the Performance tab's cohort table. */
    private function cohortBreakdown(ChairAnalyticsService $analytics): array
    {
        $breakdown = $analytics->sectionBreakdown();

        return ['by_section' => $breakdown['sections'], 'by_year' => $breakdown['years']];
    }

    private function charts(ChairDashboardService $dashboard, array $filters, ?array $tabs = null): array
    {
        return $dashboard->charts(
            Carbon::parse($filters['from']),
            Carbon::parse($filters['to']),
            $filters['subject'],
            $filters['section'],
            $filters['priority'],
            $filters['reason'],
            $tabs,
        );
    }

    /**
     * Comparison badges for the headline cards where a trend means something:
     * practising students (this week vs the week before) and subject coverage
     * (vs 30 days ago).
     */
    private function kpiComparisons(array $stats, $activeStudents): array
    {
        $cutoff = now()->subDays(self::KPI_COMPARISON_DAYS);
        $assignedThen = DB::table('faculty_subjects')
            ->where('assigned_at', '<=', $cutoff)
            ->distinct('subject_id')
            ->count('subject_id');

        $weekAgo = now()->subDays(self::PRACTISE_WINDOW_DAYS);
        $practisingBefore = $this->studentsPractising($activeStudents, $weekAgo->copy()->subDays(self::PRACTISE_WINDOW_DAYS), $weekAgo);

        return [
            'practising' => $stats['practising'] - $practisingBefore,
            'assigned'   => $stats['assigned'] - $assignedThen,
        ];
    }

    /** Distinct active students who completed any quiz in [$from, $to). */
    private function studentsPractising($studentIds, \DateTimeInterface $from, ?\DateTimeInterface $to = null): int
    {
        return DB::table('quiz_sessions')
            ->whereIn('student_id', $studentIds)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $from)
            ->when($to, fn ($query) => $query->where('completed_at', '<', $to))
            ->distinct()
            ->count('student_id');
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
