<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Models\QuizSession;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\WeaknessDetector;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StudentManagementController extends Controller
{
    private const PER_PAGE = 15;

    private const INACTIVE_DAYS = 7;

    public function __construct(private WeaknessDetector $weakness) {}

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $allRows = $this->studentRows();
        $rows = $this->applyFilters($allRows, $filters);
        $page = max(1, (int) $request->input('page', 1));
        $students = new LengthAwarePaginator(
            $rows->forPage($page, self::PER_PAGE)->values(),
            $rows->count(), self::PER_PAGE, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $withScores = $allRows->whereNotNull('score');
        $stats = [
            'total' => $allRows->count(),
            'active' => $allRows->where('is_active', true)->count(),
            'average' => $withScores->isNotEmpty() ? (int) round($withScores->avg('score')) : 0,
            'at_risk' => $allRows->where('at_risk', true)->count(),
        ];

        $groups = User::where('role_id', Role::STUDENT)
            ->join('student_profiles', 'student_profiles.user_id', '=', 'users.id')
            ->select('student_profiles.year_level', 'student_profiles.section')
            ->get();

        return view('chair.students', [
            'students' => $students,
            'stats' => $stats,
            'filters' => $filters,
            'years' => $groups->pluck('year_level')->filter()->unique()->sort()->values(),
            'sections' => $groups->pluck('section')->filter()->unique()->sort()->values(),
        ]);
    }

    public function show(int $id)
    {
        $student = User::where('role_id', Role::STUDENT)->with('studentProfile')->findOrFail($id);
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
                'subjects.code', 'subjects.name', 'subjects.passing_threshold',
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
                'topics.name as topic', 'subjects.code as subject',
                'performance_records.correct_count', 'performance_records.total_attempts',
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
        return view('chair.student-form', ['editMode' => false]);
    }

    public function store(Request $request)
    {
        $data = $this->validateStudent($request);
        $student = DB::transaction(function () use ($data) {
            $student = User::create([
                'role_id' => Role::STUDENT,
                'first_name' => $data['first_name'], 'last_name' => $data['last_name'],
                'email' => $data['email'], 'password' => Hash::make($data['password']),
                'is_active' => (bool) $data['is_active'], 'email_verified' => true,
            ]);
            $this->saveProfile($student, $data);

            return $student;
        });

        return redirect()->route('chair.students.show', $student)->with('status', 'Student account enrolled successfully.');
    }

    public function edit(int $id)
    {
        return view('chair.student-form', [
            'editMode' => true,
            'student' => User::where('role_id', Role::STUDENT)->with('studentProfile')->findOrFail($id),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $student = User::where('role_id', Role::STUDENT)->findOrFail($id);
        $data = $this->validateStudent($request, $student);
        DB::transaction(function () use ($student, $data) {
            $student->update([
                'first_name' => $data['first_name'], 'last_name' => $data['last_name'],
                'email' => $data['email'], 'is_active' => (bool) $data['is_active'],
            ]);
            if (! empty($data['password'])) {
                $student->update(['password' => Hash::make($data['password'])]);
            }
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

    public function import(Request $request)
    {
        $request->validate(['csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);
        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $header = array_map(fn ($value) => strtolower(trim((string) $value)), $header ?: []);
        $required = ['first_name', 'last_name', 'email', 'password'];
        if (array_diff($required, $header)) {
            fclose($handle);

            return back()->with('error', 'CSV must contain: first_name, last_name, email, and password columns.');
        }

        $created = 0;
        $line = 1;
        $errors = [];
        while (($values = fgetcsv($handle)) !== false) {
            $line++;
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }
            $values = array_pad($values, count($header), null);
            $row = array_combine($header, array_slice($values, 0, count($header)));
            foreach (['student_number', 'year_level', 'section', 'exam_target_date'] as $optional) {
                if (array_key_exists($optional, $row) && trim((string) $row[$optional]) === '') {
                    $row[$optional] = null;
                }
            }
            $row['is_active'] = $this->csvBoolean($row['is_active'] ?? '1');
            $row['password_confirmation'] = $row['password'] ?? null;
            $validator = Validator::make($row, $this->studentRules());
            if ($validator->fails()) {
                $errors[] = "Row {$line}: ".$validator->errors()->first();

                continue;
            }

            try {
                $data = $validator->validated();
                DB::transaction(function () use ($data) {
                    $student = User::create([
                        'role_id' => Role::STUDENT, 'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'], 'email' => $data['email'],
                        'password' => Hash::make($data['password']), 'is_active' => (bool) $data['is_active'],
                        'email_verified' => true,
                    ]);
                    $this->saveProfile($student, $data);
                });
                $created++;
            } catch (\Throwable $exception) {
                $errors[] = "Row {$line}: could not be imported.";
            }
        }
        fclose($handle);

        return back()->with('status', "{$created} student".($created === 1 ? '' : 's').' imported.')
            ->with('import_errors', array_slice($errors, 0, 20));
    }

    public function template()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['first_name', 'last_name', 'email', 'password', 'student_number', 'year_level', 'section', 'is_active']);
            fputcsv($out, ['Juan', 'Dela Cruz', 'juan@example.edu', 'ChangeMe123!', '2026-0001', '4', 'A', '1']);
            fclose($out);
        }, 'student-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportCsv(Request $request)
    {
        $rows = $this->applyFilters($this->studentRows(), $this->filters($request));

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Student Number', 'Student', 'Email', 'Year Level', 'Section', 'Readiness (%)', 'Questions Attempted', 'Quizzes', 'Streak', 'Last Active', 'At Risk', 'Status']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row['student_number'], $row['name'], $row['email'], $row['year_level'], $row['section'],
                    $row['score'], $row['attempted'], $row['quizzes'], $row['streak'],
                    $row['last_active']?->format('Y-m-d H:i'), $row['at_risk'] ? 'Yes' : 'No', $row['is_active'] ? 'Active' : 'Disabled',
                ]);
            }
            fclose($out);
        }, 'student-performance-'.now()->format('Y-m-d_His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->applyFilters($this->studentRows(), $filters);
        $scored = $rows->whereNotNull('score');
        $stats = [
            'total' => $rows->count(), 'active' => $rows->where('is_active', true)->count(),
            'average' => $scored->isNotEmpty() ? (int) round($scored->avg('score')) : 0,
            'at_risk' => $rows->where('at_risk', true)->count(),
        ];

        return view('chair.student-report', compact('rows', 'stats', 'filters'));
    }

    private function studentRows(?array $onlyIds = null)
    {
        $activity = DB::table('quiz_sessions')->where('session_type', '!=', 'training')
            ->whereNotNull('completed_at')->when($onlyIds, fn ($query) => $query->whereIn('student_id', $onlyIds))
            ->groupBy('student_id')->select(
                'student_id', DB::raw('COUNT(*) as quizzes'), DB::raw('COALESCE(SUM(total_items),0) as attempted'),
                DB::raw('COALESCE(SUM(correct_answers),0) as correct'), DB::raw('MAX(completed_at) as last_quiz')
            )->get()->keyBy('student_id');

        return User::where('role_id', Role::STUDENT)->when($onlyIds, fn ($query) => $query->whereIn('id', $onlyIds))
            ->with('studentProfile')->orderBy('first_name')->get()->map(function (User $student) use ($activity) {
                $quiz = $activity->get($student->id);
                $attempted = (int) ($quiz->attempted ?? 0);
                $score = $attempted > 0 ? (int) round((int) $quiz->correct / $attempted * 100) : null;
                $dates = collect([$quiz?->last_quiz, $student->last_login_at, $student->created_at])
                    ->filter()->map(fn ($date) => Carbon::parse($date));
                $lastActive = $dates->sortDesc()->first();
                $daysIdle = $lastActive ? (int) $lastActive->diffInDays(now()) : self::INACTIVE_DAYS;
                $low = $attempted >= WeaknessDetector::MIN_ATTEMPTS && $score < WeaknessDetector::ACCURACY_THRESHOLD * 100;
                $inactive = $daysIdle >= self::INACTIVE_DAYS;
                $profile = $student->studentProfile;

                return [
                    'id' => $student->id, 'name' => $student->name, 'initials' => strtoupper(substr($student->first_name, 0, 1).substr($student->last_name, 0, 1)),
                    'email' => $student->email, 'student_number' => $profile?->student_number, 'year_level' => $profile?->year_level,
                    'section' => $profile?->section, 'score' => $score, 'attempted' => $attempted, 'quizzes' => (int) ($quiz->quizzes ?? 0),
                    'streak' => (int) ($profile?->streak_days ?? 0), 'last_active' => $lastActive, 'days_idle' => $daysIdle,
                    'at_risk' => $student->is_active && ($low || $inactive), 'is_active' => (bool) $student->is_active,
                ];
            });
    }

    private function filters(Request $request): array
    {
        return ['search' => trim((string) $request->input('search')), 'year' => $request->input('year'),
            'section' => $request->input('section'), 'status' => $request->input('status'), 'sort' => $request->input('sort', 'name')];
    }

    private function applyFilters($rows, array $filters)
    {
        if ($filters['search'] !== '') {
            $needle = mb_strtolower($filters['search']);
            $rows = $rows->filter(fn ($r) => str_contains(mb_strtolower($r['name'].' '.$r['email'].' '.$r['student_number']), $needle));
        }
        if ($filters['year'] !== null && $filters['year'] !== '') {
            $rows = $rows->where('year_level', (int) $filters['year']);
        }
        if ($filters['section']) {
            $rows = $rows->where('section', $filters['section']);
        }
        if ($filters['status'] === 'active') {
            $rows = $rows->where('is_active', true);
        } elseif ($filters['status'] === 'disabled') {
            $rows = $rows->where('is_active', false);
        } elseif ($filters['status'] === 'at_risk') {
            $rows = $rows->where('at_risk', true);
        }
        $rows = match ($filters['sort']) {
            'score_desc' => $rows->sortByDesc(fn ($row) => $row['score'] ?? -1),
            'score_asc' => $rows->sortBy(fn ($row) => $row['score'] ?? 101),
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
            'first_name' => ['required', 'string', 'max:60'], 'last_name' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($student?->id)],
            'password' => $student ? ['nullable', 'confirmed', Password::defaults()] : ['required', 'confirmed', Password::defaults()],
            'student_number' => ['nullable', 'string', 'max:30', Rule::unique('student_profiles', 'student_number')->ignore($student?->id, 'user_id')],
            'year_level' => ['nullable', 'integer', 'between:1,6'], 'section' => ['nullable', 'string', 'max:30'],
            'exam_target_date' => ['nullable', 'date'], 'is_active' => ['required', 'boolean'],
        ];
    }

    private function saveProfile(User $student, array $data): void
    {
        StudentProfile::updateOrCreate(['user_id' => $student->id], [
            'student_number' => $data['student_number'] ?? null, 'year_level' => $data['year_level'] ?? null,
            'section' => $data['section'] ?? null, 'exam_target_date' => $data['exam_target_date'] ?? null,
        ]);
    }

    private function csvBoolean($value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'active'], true);
    }
}
