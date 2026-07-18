<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;

use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
use App\Services\WeaknessDetector;
use App\Services\ChairAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProgramChairController extends Controller
{
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
            ->where('session_type', '!=', 'training')
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
        return view('chair.faculty', [
            'faculty'  => User::where('role_id', Role::FACULTY)
                ->with('assignedSubjects')
                ->orderBy('first_name')
                ->get(),
            'subjects' => Subject::orderBy('id')->get(),
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
        ]);
    }

    /**
     * Create a faculty login and assign subjects in one step.
     */
    public function storeFaculty(Request $request)
    {
        $data = $request->validate([
            'first_name'      => 'required|string|max:60',
            'last_name'       => 'required|string|max:60',
            'email'           => 'required|email|max:120|unique:users,email',
            'password'        => ['required', 'confirmed', Password::defaults()],
            'employee_number' => 'nullable|string|max:20',
            'department'      => 'nullable|string|max:100',
            'subjects'        => 'array',
            'subjects.*'      => 'integer|exists:subjects,id',
        ]);

        DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'role_id'        => Role::FACULTY,
                'first_name'     => $data['first_name'],
                'last_name'      => $data['last_name'],
                'email'          => $data['email'],
                'password'       => Hash::make($data['password']),
                'is_active'      => true,
                'email_verified' => true,
            ]);

            $user->facultyProfile()->create([
                'employee_number' => $data['employee_number'] ?? null,
                'department'      => $data['department'] ?? 'College of Accountancy',
            ]);

            $this->syncSubjects($user, $request->input('subjects', []));
        });

        return redirect()->route('chair.faculty')
            ->with('status', 'Faculty account created. They can now log in with their email and password.');
    }

    /**
     * Show the edit form for an existing faculty account.
     */
    public function editFaculty(int $id)
    {
        $faculty = User::where('role_id', Role::FACULTY)
            ->with(['assignedSubjects', 'facultyProfile'])
            ->findOrFail($id);

        return view('chair.faculty-form', [
            'editMode' => true,
            'faculty'  => $faculty,
            'subjects' => Subject::orderBy('id')->get(),
            'assigned' => $faculty->assignedSubjects->pluck('id')->all(),
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
            'password'        => ['nullable', 'confirmed', Password::defaults()],
            'employee_number' => 'nullable|string|max:20',
            'department'      => 'nullable|string|max:100',
            'is_active'       => 'nullable|boolean',
            'subjects'        => 'array',
            'subjects.*'      => 'integer|exists:subjects,id',
        ]);

        DB::transaction(function () use ($faculty, $data, $request) {
            $faculty->update([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'is_active'  => $request->boolean('is_active'),
            ]);

            if (!empty($data['password'])) {
                $faculty->update(['password' => Hash::make($data['password'])]);
            }

            $faculty->facultyProfile()->updateOrCreate(
                ['user_id' => $faculty->id],
                [
                    'employee_number' => $data['employee_number'] ?? null,
                    'department'      => $data['department'] ?? null,
                ]
            );

            $this->syncSubjects($faculty, $request->input('subjects', []));
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
            'subjects'   => 'array',
            'subjects.*' => 'integer|exists:subjects,id',
        ]);

        $this->syncSubjects($faculty, $request->input('subjects', []));

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
     * Attach pivot rows with the chair who made the assignment + timestamp.
     */
    private function syncSubjects(User $faculty, array $subjectIds): void
    {
        $pivot = [];
        foreach ($subjectIds as $sid) {
            $pivot[(int) $sid] = [
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
            ];
        }

        $faculty->assignedSubjects()->sync($pivot);
    }
}
