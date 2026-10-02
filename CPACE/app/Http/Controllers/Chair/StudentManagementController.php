<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Concerns\GeneratesOneTimePassword;
use App\Http\Controllers\Controller;
use App\Mail\AccountCredentialsMail;
use App\Models\AlumniProfile;
use App\Models\QuizSession;
use App\Models\Role;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\BrandedXlsxReport;
use App\Services\WeaknessDetector;
use App\Support\Auditor;
use App\Support\BatchYear;
use Dompdf\Dompdf;
use Dompdf\Options as DompdfOptions;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentManagementController extends Controller
{
    use GeneratesOneTimePassword;

    private const PER_PAGE = 15;

    private const INACTIVE_DAYS = 7;

    /** Section filter value for "not in any curated section". */
    public const NO_SECTION = '__unsectioned__';

    public function __construct(private WeaknessDetector $weakness) {}

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $allRows = $this->studentRows();
        $rows = $this->applyFilters($allRows, $filters);
        $page = max(1, (int) $request->input('page', 1));
        $students = new LengthAwarePaginator(
            $rows->forPage($page, self::PER_PAGE)->values(),
            $rows->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // KPI cards count currently-enrolled students only (excludes alumni and
        // shifted-out accounts), matching the "enrolled" definition used on the
        // chair analytics cohort strip. The table below still lists every
        // account, including alumni/shifted, for record-keeping.
        $enrolledRows = $allRows->where('is_alumni', false)->where('is_shifted', false);
        $withScores = $enrolledRows->whereNotNull('score');
        $stats = [
            'total' => $enrolledRows->count(),
            'active' => $enrolledRows->where('is_active', true)->count(),
            'average' => $withScores->isNotEmpty() ? (int) round($withScores->avg('score')) : 0,
            'at_risk' => $enrolledRows->where('at_risk', true)->count(),
        ];

        $groups = User::where('role_id', Role::STUDENT)
            ->join('student_profiles', 'student_profiles.user_id', '=', 'users.id')
            ->select('student_profiles.year_level', 'student_profiles.section')
            ->get();

        // Sections are managed from this page (there's no separate Sections
        // page any more), so it also needs the curated catalog with counts.
        $catalog = SectionManagementController::catalog();

        return view('chair.students', [
            'students' => $students,
            'stats' => $stats,
            'filters' => $filters,
            'years' => $groups->pluck('year_level')->filter()->unique()->sort()->values(),
            'sections' => $groups->pluck('section')->filter()->unique()->sort()->values(),
            'sectionCatalog' => $catalog['sections'],
            'sectionStats' => $enrolledRows->whereNotNull('section')->groupBy('section')->map(function ($group) {
                $scored = $group->whereNotNull('score');

                return [
                    'readiness' => $scored->isNotEmpty() ? (int) round($scored->avg('score')) : null,
                    'at_risk' => $group->where('at_risk', true)->count(),
                ];
            }),
            // Counted with the same rule as the "Not in a section" filter, so
            // the chip's number matches the list it opens.
            'noSectionCount' => $this->applyFilters($allRows, [
                'search' => '', 'year' => null, 'section' => self::NO_SECTION, 'status' => null, 'sort' => 'name',
            ])->count(),
        ]);
    }

    /**
     * Put one or more students into a curated section (or take them out of
     * any section when section_id is empty). Year level follows the section,
     * same as adding students from a section's roster.
     */
    public function assignSection(Request $request)
    {
        $request->validate([
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')],
        ]);

        // Same "select all matching filters" handling as bulkMarkAlumni().
        if ($request->boolean('select_all')) {
            $ids = $this->applyFilters($this->studentRows(), $this->filters($request))
                ->where('is_alumni', false)->pluck('id')->values()->all();
        } else {
            $ids = $request->validate([
                'student_ids' => ['required', 'array', 'min:1'],
                'student_ids.*' => ['integer'],
            ])['student_ids'];
        }

        // Alumni and shifted-out students keep the section they left with.
        $userIds = StudentProfile::query()
            ->join('users', 'users.id', '=', 'student_profiles.user_id')
            ->where('users.role_id', Role::STUDENT)
            ->where('student_profiles.is_alumni', false)
            ->where('student_profiles.is_shifted', false)
            ->whereIn('users.id', $ids)
            ->pluck('users.id');

        if ($userIds->isEmpty()) {
            return back()->with('error', 'No students who can be sectioned were selected.');
        }

        $section = $request->filled('section_id') ? Section::find($request->input('section_id')) : null;

        StudentProfile::whereIn('user_id', $userIds)->update($section
            ? ['section' => $section->name, 'year_level' => $section->year_level]
            : ['section' => null]);

        $count = $userIds->count();
        $who = $count === 1 ? '1 student was' : $count . ' students were';

        return back()->with('status', $section
            ? "{$who} placed in {$section->name}."
            : "{$who} removed from their section.");
    }

    public function show(int $id)
    {
        $student = User::where('role_id', Role::STUDENT)->with('studentProfile')->findOrFail($id);
        Auditor::log(Auth::user(), 'student_viewed', "Viewed {$student->name}'s student record.", 'User', $student->id);
        $row = $this->studentRows([$student->id])->first();

        $subjectPerformance = DB::table('subjects')
            ->leftJoin('topics', 'topics.subject_id', '=', 'subjects.id')
            ->leftJoin('performance_records', function ($join) use ($student) {
                $join->on('performance_records.topic_id', '=', 'topics.id')
                    ->where('performance_records.student_id', $student->id);
            })
            ->groupBy('subjects.id', 'subjects.code', 'subjects.name', 'subjects.passing_threshold')
            ->orderBy('subjects.id')
            ->select(
                'subjects.code',
                'subjects.name',
                'subjects.passing_threshold',
                DB::raw('COALESCE(SUM(performance_records.correct_count),0) as correct'),
                DB::raw('COALESCE(SUM(performance_records.total_attempts),0) as attempted')
            )->get()->map(function ($subject) {
                $subject->accuracy = $subject->attempted > 0
                    ? (int) round($subject->correct / $subject->attempted * 100) : 0;
                $subject->passing = $subject->attempted > 0 && $subject->accuracy >= $subject->passing_threshold;

                return $subject;
            });

        $weakAreas = DB::table('performance_records')
            ->join('topics', 'topics.id', '=', 'performance_records.topic_id')
            ->join('subjects', 'subjects.id', '=', 'topics.subject_id')
            ->where('performance_records.student_id', $student->id)
            ->where('performance_records.total_attempts', '>', 0)
            ->select(
                'topics.name as topic',
                'subjects.code as subject',
                'performance_records.correct_count',
                'performance_records.total_attempts',
                'performance_records.consecutive_wrong'
            )->get()->filter(function ($record) {
                [$weak] = $this->weakness->evaluate($record);

                return $weak;
            })->map(function ($record) {
                $record->accuracy = (int) round($record->correct_count / max(1, $record->total_attempts) * 100);

                return $record;
            })->sortBy('accuracy')->values();

        $quizHistory = QuizSession::with('subject')
            ->where('student_id', $student->id)->whereNotNull('completed_at')
            ->orderByDesc('completed_at')->paginate(10);

        return view('chair.student-detail', compact('student', 'row', 'subjectPerformance', 'weakAreas', 'quizHistory'));
    }

    public function create()
    {
        return view('chair.student-form', [
            'editMode' => false,
            'sections' => Section::where('is_active', true)->orderBy('year_level')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateStudent($request);
        $tempPassword = $this->generateOneTimePassword();
        $student = DB::transaction(function () use ($data, $tempPassword) {
            $student = User::create([
                'role_id' => Role::STUDENT,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($tempPassword),
                'is_active' => (bool) $data['is_active'],
                'email_verified' => true,
                'setup_completed_at' => null,
                'temp_password' => $tempPassword,
            ]);
            $this->saveProfile($student, $data);

            return $student;
        });

        $mailed = $this->mailCredentials($student, $tempPassword, 'student');

        return redirect()->route('chair.students.show', $student)->with(
            'status',
            $mailed
                ? "Student account enrolled successfully. The one-time password was emailed to {$student->email}."
                : "Student account enrolled, but the credentials email couldn't be sent. Use \"Resend OTP\" on this student's row to try again."
        );
    }

    public function edit(int $id)
    {
        $student = User::where('role_id', Role::STUDENT)->with(['studentProfile', 'alumniProfile'])->findOrFail($id);
        $sections = Section::orderByDesc('is_active')->orderBy('year_level')->orderBy('name')->get();

        // The student's current section might be inactive or, for older
        // records, a legacy string that was never added to the curated
        // catalog - keep it selectable so saving the form without touching
        // this field doesn't silently blank it out or fail validation.
        $currentSection = $student->studentProfile?->section;
        if ($currentSection && ! $sections->contains('name', $currentSection)) {
            $sections->push(new Section(['name' => $currentSection, 'is_active' => false]));
        }

        return view('chair.student-form', [
            'editMode' => true,
            'student' => $student,
            'sections' => $sections,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $student = User::where('role_id', Role::STUDENT)->findOrFail($id);
        $data = $this->validateStudent($request, $student);
        DB::transaction(function () use ($student, $data) {
            $student->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                // A student who just shifted out of the program is locked
                // out regardless of what the "account enabled" toggle said.
                'is_active' => ! empty($data['is_shifted']) ? false : (bool) $data['is_active'],
            ]);
            $this->saveProfile($student, $data);
        });

        return redirect()->route('chair.students.show', $student)->with('status', 'Student account updated.');
    }

    public function toggle(int $id)
    {
        $student = User::where('role_id', Role::STUDENT)->findOrFail($id);
        $student->update(['is_active' => ! $student->is_active]);

        return back()->with('status', $student->is_active ? 'Student account enabled.' : 'Student account disabled.');
    }

    /**
     * Marks every checked student as Alumni in one go, instead of opening each
     * account's edit form individually. Students already marked keep their
     * original `alumni_marked_at` (same rule as the single-account form); a
     * blank AlumniProfile is created for anyone who doesn't have one yet so
     * the Alumni directory has a row to show.
     */
    public function bulkMarkAlumni(Request $request)
    {
        // "Select all matching filters" mode: the checkbox in the header only
        // ever reaches the rows rendered on the current page, so when the
        // chair confirms "select all N students" we re-run the same
        // search/year/section/status filters used by index() instead of
        // trusting whatever ids happened to be on that page.
        if ($request->boolean('select_all')) {
            $filters = $this->filters($request);
            $rows = $this->applyFilters($this->studentRows(), $filters)
                ->where('is_alumni', false);
            $data = ['student_ids' => $rows->pluck('id')->values()->all()];
        } else {
            $data = $request->validate([
                'student_ids' => ['required', 'array', 'min:1'],
                'student_ids.*' => ['integer'],
            ]);
        }

        if (empty($data['student_ids'])) {
            return back()->with('error', 'No matching students were selected.');
        }

        $students = User::where('role_id', Role::STUDENT)
            ->whereIn('id', $data['student_ids'])
            ->with('studentProfile')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No matching students were selected.');
        }

        $marked = 0;
        DB::transaction(function () use ($students, &$marked) {
            foreach ($students as $student) {
                $profile = $student->studentProfile;
                if ($profile?->is_alumni) {
                    continue; // already alumni — leave alumni_marked_at as is
                }

                StudentProfile::updateOrCreate(['user_id' => $student->id], [
                    'is_alumni' => true,
                    'alumni_marked_at' => now(),
                ]);
                AlumniProfile::firstOrCreate(['user_id' => $student->id]);
                $marked++;
            }
        });

        $skipped = $students->count() - $marked;
        $status = $marked . ' student' . ($marked === 1 ? '' : 's') . ' marked as Alumni.';
        if ($skipped > 0) {
            $status .= ' ' . $skipped . ' were already Alumni and left unchanged.';
        }

        return back()->with('status', $status);
    }

    /**
     * Issue a fresh one-time password for a student who is still pending
     * first-login setup. Covers accounts enrolled before OTPs were persisted
     * (only the bcrypt hash was kept, so the original can't be recovered) as
     * well as a student who simply lost their original OTP.
     */
    public function regenerateOtp(int $id)
    {
        $student = User::where('role_id', Role::STUDENT)->findOrFail($id);

        if ($student->setup_completed_at !== null) {
            return back()->with('error', 'This account has already completed setup.');
        }

        $tempPassword = $this->generateOneTimePassword();
        $student->update([
            'password' => Hash::make($tempPassword),
            'temp_password' => $tempPassword,
        ]);

        $mailed = $this->mailCredentials($student, $tempPassword, 'student', reissue: true);

        return back()->with(
            'status',
            $mailed
                ? "A new one-time password was emailed to {$student->name} ({$student->email})."
                : "A new one-time password was generated for {$student->name}, but the email couldn't be sent. Try \"Resend OTP\" again."
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
     * Bulk-enroll students from an uploaded class list. For every valid row we
     * provision an account with a generated one-time password (which the
     * student must change on first login) and hand the credentials back once.
     *
     * The chair reviews (and can edit) the parsed rows in the browser first —
     * the browser posts back the edited rows as `rows_json` rather than
     * re-uploading the original file, so corrections made in the preview
     * table are what actually get created. `rows_json` is only produced for
     * files the browser can parse itself (CSV); anything else falls back to
     * the server parsing the uploaded file directly, unedited.
     */
    public function import(Request $request)
    {
        if ($request->filled('rows_json')) {
            $decoded = json_decode((string) $request->input('rows_json'), true);
            if (! is_array($decoded)) {
                return back()->with('error', 'The submitted student list was invalid. Please re-upload your file.');
            }

            $rows = array_values(array_filter(array_map(fn($row) => [
                'first_name' => trim((string) ($row['first_name'] ?? '')),
                'last_name' => trim((string) ($row['last_name'] ?? '')),
                'email' => strtolower(trim((string) ($row['email'] ?? ''))),
                'student_number' => trim((string) ($row['student_number'] ?? '')) ?: null,
                'section' => trim((string) ($row['section'] ?? '')) ?: null,
            ], $decoded), fn($row) => $row['first_name'] !== '' || $row['last_name'] !== ''));

            if (empty($rows)) {
                return back()->with('error', 'No student rows were submitted.');
            }
        } else {
            $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);
            $rows = $this->parseCsvRows($request->file('file')->getRealPath());
            if ($rows === null) {
                return back()->with('error', 'The file must contain at least first_name and last_name columns.');
            }
        }

        // Coming from a section's "Add students": rows that don't name a
        // section go into that one (only if it's a real section).
        $defaultSection = trim((string) $request->input('default_section'));
        if ($defaultSection !== '' && Section::where('name', $defaultSection)->exists()) {
            $rows = array_map(fn($row) => ['section' => ($row['section'] ?? null) ?: $defaultSection] + $row, $rows);
        }

        [$created, $errors] = $this->createAccountsFromRows($rows);

        $failedMail = count(array_filter($created, fn($row) => ! $row['mailed']));
        $status = count($created) . ' account' . (count($created) === 1 ? '' : 's') . ' created.';
        if ($failedMail > 0) {
            $status .= " {$failedMail} credential email" . ($failedMail === 1 ? '' : 's') . " couldn't be sent — see the flagged rows below.";
        }

        return back()
            ->with('created_credentials', $created)
            ->with('status', $status)
            ->with('import_errors', array_slice($errors, 0, 25));
    }

    /**
     * Reads an uploaded CSV into the same plain row shape used by the
     * `rows_json` path, without creating anything yet. Returns null when the
     * file is missing the required header columns.
     */
    private function parseCsvRows(string $path): ?array
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        $header = array_map(fn($value) => strtolower(trim((string) $value)), $header ?: []);

        if (! in_array('first_name', $header, true) || ! in_array('last_name', $header, true)) {
            fclose($handle);

            return null;
        }

        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count(array_filter($values, fn($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }
            $values = array_pad($values, count($header), null);
            $row = array_combine($header, array_slice($values, 0, count($header)));

            $rows[] = [
                'first_name' => trim((string) ($row['first_name'] ?? '')),
                'last_name' => trim((string) ($row['last_name'] ?? '')),
                'email' => strtolower(trim((string) ($row['email'] ?? ''))),
                'student_number' => trim((string) ($row['student_number'] ?? '')) ?: null,
                'section' => trim((string) ($row['section'] ?? '')) ?: null,
            ];
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Shared by both import paths: validates each row and, if it passes,
     * creates the student account and emails (or falls back to returning)
     * the one-time password. Returns [created[], errors[]].
     */
    private function createAccountsFromRows(array $rows): array
    {
        $created = [];
        $errors = [];

        foreach ($rows as $i => $row) {
            $line = $i + 1;
            $firstName = $row['first_name'];
            $lastName = $row['last_name'];
            $email = $row['email'];
            $studentNumber = $row['student_number'];
            $section = $row['section'];

            if ($firstName === '' || $lastName === '') {
                $errors[] = "Row {$line}: missing first or last name.";

                continue;
            }
            if ($email === '') {
                $email = $this->generateEmail($firstName, $lastName, $studentNumber);
            }

            $validator = Validator::make(
                ['first_name' => $firstName, 'last_name' => $lastName, 'email' => $email, 'student_number' => $studentNumber],
                [
                    'first_name' => ['required', 'string', 'max:60'],
                    'last_name' => ['required', 'string', 'max:60'],
                    'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')],
                    'student_number' => ['nullable', 'string', 'max:30', Rule::unique('student_profiles', 'student_number')],
                ]
            );
            if ($validator->fails()) {
                $errors[] = "Row {$line}: " . $validator->errors()->first();

                continue;
            }

            try {
                $tempPassword = $this->generateOneTimePassword();
                $student = DB::transaction(function () use ($firstName, $lastName, $email, $studentNumber, $section, $tempPassword) {
                    $student = User::create([
                        'role_id' => Role::STUDENT,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'password' => Hash::make($tempPassword),
                        'is_active' => true,
                        'email_verified' => true,
                        'setup_completed_at' => null, // must run first-login Account Setup
                        'temp_password' => $tempPassword,
                    ]);
                    $profile = ['student_number' => $studentNumber, 'section' => $section];
                    if (BatchYear::columnExists()) {
                        $profile['batch_year'] = BatchYear::forDate(now());
                    }
                    StudentProfile::updateOrCreate(['user_id' => $student->id], $profile);

                    return $student;
                });

                $mailed = $this->mailCredentials($student, $tempPassword, 'student');

                $created[] = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'student_number' => $studentNumber,
                    'section' => $section,
                    'mailed' => $mailed,
                    // Only ever exposed to the chair as a manual fallback when the email failed to send.
                    'temp_password' => $mailed ? null : $tempPassword,
                ];
            } catch (\Throwable $exception) {
                $errors[] = "Row {$line}: could not be imported.";
            }
        }

        return [$created, $errors];
    }

    public function template()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['first_name', 'last_name', 'email', 'student_number', 'section']);
            fputcsv($out, ['Juan', 'Dela Cruz', '', '23-00001', 'BSA-4A']);
            fputcsv($out, ['Maria', 'Santos', '', '23-00002', 'BSA-4A']);
            fclose($out);
        }, 'student-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Build a GSuite-style address when a row leaves the email blank.
     * Students are identified by their SR-Code (e.g. "23-70362@g.batstate-u.edu.ph"),
     * falling back to a name-based address when no student number is given.
     */
    private function generateEmail(string $first, string $last, ?string $studentNumber = null): string
    {
        if ($studentNumber) {
            $slugNumber = preg_replace('/[^a-z0-9\-]/', '', strtolower($studentNumber));

            if ($slugNumber !== '') {
                return $slugNumber . '@g.batstate-u.edu.ph';
            }
        }

        $slug = fn($value) => preg_replace('/[^a-z0-9]/', '', strtolower($value));
        $base = $slug($first) . '.' . $slug($last);

        return $base . '@cpace.edu';
    }

    /**
     * The filtered roster as a designed Excel report: maroon CPACE banner with
     * the logo, who/when/scope lines, summary figures, and a styled table.
     * (A plain CSV can't carry a logo or formatting, hence .xlsx.)
     */
    public function exportExcel(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->applyFilters($this->studentRows(), $filters)->values();
        $stats = $this->reportStats($rows);

        $report = (new BrandedXlsxReport('Student Performance Report'))->withLogo(public_path('images/cpace_logo.png'));
        $sheet = $report->sheet('Students');
        $headers = ['Student No.', 'Student', 'Email', 'Year Level', 'Section', 'Readiness', 'Questions Answered', 'Quizzes', 'Streak (days)', 'Last Active', 'Status'];

        $next = $report->writeBanner($sheet, 'Student Performance Report', [
            'Generated ' . now()->format('F j, Y g:i A') . ' by ' . Auth::user()->name,
            'Scope: ' . $this->scopeLabel($filters),
        ], count($headers));
        $next = $report->writeSummaryStrip($sheet, $next, [
            'Students' => $stats['total'],
            'Active Accounts' => $stats['active'],
            'Avg. Readiness' => $stats['average'] . '%',
            'At Risk' => $stats['at_risk'],
        ]);

        $tableStart = $next;
        $report->writeTable($sheet, $tableStart, $headers, $rows->map(fn ($row) => [
            $row['student_number'] ?: '—',
            $row['name'],
            $row['email'],
            $row['year_level'] ? (Section::YEAR_LABELS[$row['year_level']] ?? 'Year ' . $row['year_level']) : '—',
            $row['section'] ?: 'No section',
            $row['score'] === null ? 'Not rated' : $row['score'] . '%',
            $row['attempted'],
            $row['quizzes'],
            $row['streak'],
            $row['last_active']?->format('M j, Y g:i A') ?? 'Never',
            $this->statusLabel($row),
        ])->all(), [1 => 14, 2 => 26, 3 => 32, 4 => 12, 5 => 13, 6 => 12, 7 => 12, 8 => 10, 9 => 11, 10 => 20, 11 => 15]);

        // Colour the Status column the way the page does.
        $colors = ['At Risk' => 'FFB91C1C', 'On Track' => 'FF047857', 'Setup Pending' => 'FFB45309', 'Disabled' => 'FF6B7280', 'Alumni' => 'FF4338CA'];
        foreach ($rows as $i => $row) {
            $sheet->getStyle('K' . ($tableStart + 1 + $i))->getFont()->setBold(true)
                ->getColor()->setARGB($colors[$this->statusLabel($row)] ?? 'FF333333');
        }

        return $report->download($this->reportFilename($filters, 'xlsx'));
    }

    /** The filtered roster as a designed, downloadable PDF (A4 landscape). */
    public function exportPdf(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->applyFilters($this->studentRows(), $filters)->values();

        // The maroon crest: cpace_logo.png is the white version for dark backgrounds.
        $logo = public_path('images/cpace-crest.png');
        $html = view('chair.student-report', [
            'rows' => $rows,
            'stats' => $this->reportStats($rows),
            'scope' => $this->scopeLabel($filters),
            'statusOf' => fn ($row) => $this->statusLabel($row),
            // Embedded so dompdf never has to fetch anything remotely.
            'logo' => is_file($logo) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logo)) : null,
            'preparedBy' => Auth::user()->name,
        ])->render();

        $options = new DompdfOptions();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // "Page X of Y" in the footer (dompdf can't count total pages in CSS).
        // Right-aligned with the 12mm page margin, on the footer text's line.
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $width = $dompdf->getFontMetrics()->getTextWidth('Page 99 of 99', $font, 5.3);
        $canvas->page_text($canvas->get_width() - 34 - $width, $canvas->get_height() - 35.5, 'Page {PAGE_NUM} of {PAGE_COUNT}',
            $font, 5.3, [0.61, 0.64, 0.69]);

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->reportFilename($filters, 'pdf') . '"',
        ]);
    }

    /** Headline figures for exports — currently-enrolled students only, like the KPI cards. */
    private function reportStats($rows): array
    {
        $enrolled = $rows->where('is_alumni', false)->where('is_shifted', false);
        $scored = $enrolled->whereNotNull('score');

        return [
            'total' => $enrolled->count(),
            'active' => $enrolled->where('is_active', true)->count(),
            'average' => $scored->isNotEmpty() ? (int) round($scored->avg('score')) : 0,
            'at_risk' => $enrolled->where('at_risk', true)->count(),
        ];
    }

    /** Same order of precedence as the Status column on the page. */
    private function statusLabel(array $row): string
    {
        return match (true) {
            $row['is_alumni'] => 'Alumni',
            ! $row['is_active'] => 'Disabled',
            ! $row['setup_completed'] => 'Setup Pending',
            $row['at_risk'] => 'At Risk',
            default => 'On Track',
        };
    }

    /** e.g. "Section BSA 4101 · At Risk · Search: cruz" — what an export covers. */
    private function scopeLabel(array $filters): string
    {
        $statuses = [
            'at_risk' => 'At Risk', 'low_score' => 'Low Scores', 'inactive' => 'Inactive 7+ Days',
            'setup_pending' => 'Setup Pending', 'active' => 'Active Accounts', 'disabled' => 'Disabled Accounts',
        ];

        return collect([
            match (true) {
                $filters['section'] === self::NO_SECTION => 'Students not in a section',
                (bool) $filters['section'] => 'Section ' . $filters['section'],
                default => 'All sections',
            },
            filled($filters['year']) ? (Section::YEAR_LABELS[(int) $filters['year']] ?? 'Year ' . $filters['year']) : null,
            $statuses[$filters['status']] ?? 'All statuses',
            $filters['search'] !== '' ? 'Search: ' . $filters['search'] : null,
        ])->filter()->implode(' · ');
    }

    private function reportFilename(array $filters, string $extension): string
    {
        $section = $filters['section'] && $filters['section'] !== self::NO_SECTION
            ? '-' . trim(preg_replace('/[^A-Za-z0-9]+/', '-', $filters['section']), '-')
            : '';

        return 'cpace-students' . $section . '-' . now()->format('Y-m-d') . '.' . $extension;
    }

    private function studentRows(?array $onlyIds = null)
    {
        $activity = DB::table('quiz_sessions')->where('session_type', '!=', 'training')->where('is_practice_room', false)
            ->whereNotNull('completed_at')->when($onlyIds, fn($query) => $query->whereIn('student_id', $onlyIds))
            ->groupBy('student_id')->select(
                'student_id',
                DB::raw('COUNT(*) as quizzes'),
                DB::raw('COALESCE(SUM(total_items),0) as attempted'),
                DB::raw('COALESCE(SUM(correct_answers),0) as correct'),
                DB::raw('MAX(completed_at) as last_quiz')
            )->get()->keyBy('student_id');

        return User::where('role_id', Role::STUDENT)->when($onlyIds, fn($query) => $query->whereIn('id', $onlyIds))
            ->with('studentProfile')->orderBy('first_name')->get()->map(function (User $student) use ($activity) {
                $quiz = $activity->get($student->id);
                $attempted = (int) ($quiz->attempted ?? 0);
                $score = $attempted > 0 ? (int) round((int) $quiz->correct / $attempted * 100) : null;
                $dates = collect([$quiz?->last_quiz, $student->last_login_at, $student->created_at])
                    ->filter()->map(fn($date) => Carbon::parse($date));
                $lastActive = $dates->sortDesc()->first();
                $daysIdle = $lastActive ? (int) $lastActive->diffInDays(now()) : self::INACTIVE_DAYS;
                $low = $attempted >= WeaknessDetector::MIN_ATTEMPTS && $score < WeaknessDetector::ACCURACY_THRESHOLD * 100;
                $inactive = $daysIdle >= self::INACTIVE_DAYS;
                $profile = $student->studentProfile;

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'initials' => strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)),
                    'email' => $student->email,
                    'student_number' => $profile?->student_number,
                    'year_level' => $profile?->year_level,
                    'section' => $profile?->section,
                    'score' => $score,
                    'attempted' => $attempted,
                    'quizzes' => (int) ($quiz->quizzes ?? 0),
                    'streak' => (int) ($profile?->streak_days ?? 0),
                    'last_active' => $lastActive,
                    'days_idle' => $daysIdle,
                    'at_risk' => $student->is_active && ($low || $inactive),
                    // The two halves of "at risk", kept apart so the section
                    // cards can show why students are struggling.
                    'low_score' => $student->is_active && $low,
                    'inactive' => $student->is_active && $inactive,
                    'is_active' => (bool) $student->is_active,
                    'is_alumni' => (bool) ($profile?->is_alumni ?? false),
                    'is_shifted' => (bool) ($profile?->is_shifted ?? false),
                    'setup_completed' => $student->setup_completed_at !== null,
                    // Manual fallback for the chair to read the OTP directly when a
                    // "sent" email never actually reaches the student (bounce/spam).
                    'temp_password' => $student->setup_completed_at === null ? $student->temp_password : null,
                ];
            });
    }

    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->input('search')),
            'year' => $request->input('year'),
            'section' => $request->input('section'),
            'status' => $request->input('status'),
            'sort' => $request->input('sort', 'name')
        ];
    }

    private function applyFilters($rows, array $filters)
    {
        if ($filters['search'] !== '') {
            $needle = mb_strtolower($filters['search']);
            $rows = $rows->filter(fn($r) => str_contains(mb_strtolower($r['name'] . ' ' . $r['email'] . ' ' . $r['student_number']), $needle));
        }
        if ($filters['year'] !== null && $filters['year'] !== '') {
            $rows = $rows->where('year_level', (int) $filters['year']);
        }
        if ($filters['section'] === self::NO_SECTION) {
            // Enrolled students whose section isn't one of the curated
            // sections (never set, or a legacy/typo value).
            $known = Section::pluck('name')->all();
            $rows = $rows->filter(fn($r) => ! $r['is_alumni'] && ! $r['is_shifted'] && ! in_array($r['section'], $known, true));
        } elseif ($filters['section']) {
            $rows = $rows->where('section', $filters['section']);
        }
        if ($filters['status'] === 'active') {
            $rows = $rows->where('is_active', true);
        } elseif ($filters['status'] === 'disabled') {
            $rows = $rows->where('is_active', false);
        } elseif ($filters['status'] === 'at_risk') {
            $rows = $rows->where('at_risk', true);
        } elseif ($filters['status'] === 'setup_pending') {
            $rows = $rows->where('setup_completed', false);
        } elseif ($filters['status'] === 'low_score') {
            $rows = $rows->where('low_score', true);
        } elseif ($filters['status'] === 'inactive') {
            $rows = $rows->where('inactive', true);
        }
        $rows = match ($filters['sort']) {
            'score_desc' => $rows->sortByDesc(fn($row) => $row['score'] ?? -1),
            'score_asc' => $rows->sortBy(fn($row) => $row['score'] ?? 101),
            'recent' => $rows->sortByDesc('last_active'),
            default => $rows->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE),
        };

        return $rows->values();
    }

    private function validateStudent(Request $request, ?User $student = null): array
    {
        return $request->validate($this->studentRules($student));
    }

    private function studentRules(?User $student = null): array
    {
        return [
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($student?->id)],
            'student_number' => ['nullable', 'string', 'max:30', Rule::unique('student_profiles', 'student_number')->ignore($student?->id, 'user_id')],
            'year_level' => ['nullable', 'integer', 'between:1,6'],
            'section' => ['nullable', 'string', 'max:30', Rule::exists('sections', 'name')],
            // Enrollment batch ("2026-2027"). Not the alumni `batch_year` below,
            // which is a graduation year.
            'student_batch' => ['nullable', 'string', function ($attribute, $value, $fail) {
                if (! BatchYear::isValid($value)) {
                    $fail('Enter the batch as a school year, e.g. ' . BatchYear::current() . '.');
                }
            }],
            'exam_target_date' => ['nullable', 'date'],
            'is_active' => ['required', 'boolean'],
            'is_alumni' => ['nullable', 'boolean'],
            'batch_year' => ['nullable', 'integer', 'digits:4', 'between:1980,2100'],
            'current_job' => ['nullable', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'is_shifted' => ['nullable', 'boolean'],
            'shift_reason' => ['nullable', 'string', 'max:500', 'required_if:is_shifted,1'],
        ];
    }

    /**
     * Persists enrollment fields plus the Alumni/Shifted status toggles. The
     * "marked at" timestamps are only stamped the moment a toggle flips from
     * false to true, so re-saving the form afterwards doesn't reset them.
     */
    private function saveProfile(User $student, array $data): void
    {
        $profile = $student->studentProfile;
        $wasAlumni = (bool) ($profile?->is_alumni ?? false);
        $wasShifted = (bool) ($profile?->is_shifted ?? false);
        $isAlumni = (bool) ($data['is_alumni'] ?? false);
        $isShifted = (bool) ($data['is_shifted'] ?? false);

        $fields = [
            'student_number' => $data['student_number'] ?? null,
            'year_level' => $data['year_level'] ?? null,
            'section' => $data['section'] ?? null,
            'exam_target_date' => $data['exam_target_date'] ?? null,
            'is_alumni' => $isAlumni,
            'alumni_marked_at' => $isAlumni
                ? ($wasAlumni ? $profile?->alumni_marked_at : now())
                : null,
            'is_shifted' => $isShifted,
            'shift_reason' => $isShifted ? ($data['shift_reason'] ?? null) : null,
            'shifted_at' => $isShifted
                ? ($wasShifted ? $profile?->shifted_at : now())
                : null,
        ];

        // Batch is set automatically from the enrollment date the first time
        // and never silently recomputed; the chair may override it for
        // transferees / irregular students.
        if (BatchYear::columnExists()) {
            $fields['batch_year'] = ! empty($data['student_batch'])
                ? $data['student_batch']
                : ($profile?->batch_year ?? BatchYear::forDate($student->created_at ?? now()));
        }

        StudentProfile::updateOrCreate(['user_id' => $student->id], $fields);

        if ($isAlumni) {
            AlumniProfile::updateOrCreate(['user_id' => $student->id], [
                'batch_year' => $data['batch_year'] ?? null,
                'current_job' => $data['current_job'] ?? null,
                'company' => $data['company'] ?? null,
            ]);
        }
    }

    private function csvBoolean($value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'active'], true);
    }
}
