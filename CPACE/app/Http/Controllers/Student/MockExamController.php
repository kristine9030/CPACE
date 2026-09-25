<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\SubjectTheme;
use App\Http\Controllers\Controller;
use App\Models\MockExam;
use App\Models\MockExamAttempt;
use App\Models\MockExamEvent;
use App\Models\MockExamRegistration;
use App\Models\Subject;
use App\Services\PerformanceRecorder;
use App\Services\SpacedRepetitionScheduler;
use App\Services\WeaknessDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Student side of the mock exam workflow.
 *
 * A student redeems the day's code once; that registers them for the EVENT,
 * which covers every subject exam published for that date (including any the
 * Chair publishes later the same day). Each sitting then opens only inside its
 * own scheduled window.
 *
 * Grading is deliberately self-contained: a mock exam does NOT create a
 * quiz_sessions row, so it stays out of normal quiz history and off the
 * leaderboard, and awards no points. It does feed performance_records, weak-area
 * detection and spaced repetition, because a student who bombed Inventory in
 * the mock genuinely is weak on Inventory.
 */
class MockExamController extends Controller
{
    use SubjectTheme;

    /** Subject folders for everything the student has redeemed, plus the redeem box. */
    public function index()
    {
        $student = Auth::user();
        $this->assertStudent();

        $exams = $this->registeredExams($student->id);
        $attempts = $this->attemptsFor($student->id, $exams->pluck('id'));

        $folders = $exams->groupBy('subject_id')->map(function ($group) use ($attempts) {
            $subject = $group->first()->subject;

            return [
                'subject' => $subject,
                'icon' => self::subjectIcon($subject?->code),
                'theme' => self::theme($subject?->code),
                'total' => $group->count(),
                'upcoming' => $group->filter(fn (MockExam $e) => $e->window() === 'upcoming')->count(),
                'open' => $group->filter(fn (MockExam $e) => $e->window() === 'open')->count(),
                'done' => $group->filter(fn (MockExam $e) => $attempts->get($e->id)?->isSubmitted())->count(),
                'next' => $group->sortBy('scheduled_at')->first(),
            ];
        })->values();

        return view('student.mock-exams', [
            'folders' => $folders,
            'hasAny' => $exams->isNotEmpty(),
            'isAlumniLocked' => $student->hasAlumniAccess(),
        ]);
    }

    /** Redeem the day's code. */
    public function redeem(Request $request)
    {
        $student = Auth::user();
        $this->assertStudent();
        abort_if($student->hasAlumniAccess(), 403, 'Mock exams are locked for alumni accounts.');

        $data = $request->validate([
            'access_code' => ['required', 'string', 'max:60'],
        ], [
            'access_code.required' => 'Enter the code your faculty gave you.',
        ]);

        $code = MockExamEvent::normaliseCode($data['access_code']);
        $event = MockExamEvent::where('access_code', $code)->first();

        if (! $event) {
            return back()->withErrors(['access_code' => 'That code was not recognised. Double-check it with your faculty.'])->withInput();
        }

        // Nothing published for the day yet means the code exists but has no
        // exam behind it - treat it as a clear "not ready" rather than an error.
        if ($event->exams()->where('status', MockExam::STATUS_PUBLISHED)->doesntExist()) {
            return back()->withErrors(['access_code' => 'That exam day has no published exams yet. Try again closer to the schedule.'])->withInput();
        }

        // Redeeming twice is harmless and common (students re-paste the code),
        // so it succeeds quietly instead of erroring.
        MockExamRegistration::firstOrCreate(
            ['event_id' => $event->id, 'student_id' => $student->id],
            ['redeemed_at' => now()]
        );

        return redirect()->route('mock-exams')
            ->with('status', 'Code redeemed — your exams for ' . $event->exam_date->format('M j, Y') . ' are now listed.');
    }

    /** One subject's folder: every redeemed exam for that subject. */
    public function subject(Subject $subject)
    {
        $student = Auth::user();
        $this->assertStudent();

        $exams = $this->registeredExams($student->id)
            ->where('subject_id', $subject->id)
            ->sortBy('scheduled_at')
            ->values();

        return view('student.mock-exam-subject', [
            'subject' => $subject,
            'icon' => self::subjectIcon($subject->code),
            'theme' => self::theme($subject->code),
            'exams' => $exams,
            'attempts' => $this->attemptsFor($student->id, $exams->pluck('id')),
        ]);
    }

    /** Exam detail: schedule, rules, and the consent gate before entry. */
    public function show(MockExam $mockExam)
    {
        $student = Auth::user();
        $this->assertRegistered($mockExam, $student->id);

        $attempt = MockExamAttempt::where('exam_id', $mockExam->id)->where('student_id', $student->id)->first();

        return view('student.mock-exam-show', [
            'exam' => $mockExam->load('subject'),
            'subject' => $mockExam->subject,
            'icon' => self::subjectIcon($mockExam->subject?->code),
            'theme' => self::theme($mockExam->subject?->code),
            'attempt' => $attempt,
            'window' => $mockExam->window(),
        ]);
    }

    /**
     * Begin the sitting. The attempt row is created here rather than on the
     * take screen so started_at is set once, server-side, and reloading the
     * runner can never restart the clock.
     */
    public function start(MockExam $mockExam)
    {
        $student = Auth::user();
        $this->assertRegistered($mockExam, $student->id);

        if ($mockExam->window() !== 'open') {
            return back()->withErrors([
                'exam' => $mockExam->window() === 'upcoming'
                    ? 'This exam has not started yet.'
                    : 'This exam window has closed.',
            ]);
        }

        $attempt = MockExamAttempt::firstOrCreate(
            ['exam_id' => $mockExam->id, 'student_id' => $student->id],
            ['started_at' => now(), 'status' => MockExamAttempt::STATUS_IN_PROGRESS, 'answers' => []]
        );

        if ($attempt->isSubmitted()) {
            return redirect()->route('mock-exams.result', $mockExam);
        }

        return redirect()->route('mock-exams.take', $mockExam);
    }

    /** The exam runner. */
    public function take(MockExam $mockExam)
    {
        $student = Auth::user();
        $this->assertRegistered($mockExam, $student->id);

        $attempt = MockExamAttempt::where('exam_id', $mockExam->id)->where('student_id', $student->id)->first();
        if (! $attempt) {
            return redirect()->route('mock-exams.show', $mockExam);
        }
        if ($attempt->isSubmitted()) {
            return redirect()->route('mock-exams.result', $mockExam);
        }

        return view('student.mock-exam-take', [
            'exam' => $mockExam->load('subject'),
            'subject' => $mockExam->subject,
            'theme' => self::theme($mockExam->subject?->code),
            'attempt' => $attempt,
            'items' => $mockExam->items()->get(),
            'answers' => (array) ($attempt->answers ?? []),
            // Authoritative seconds left, computed from the server's own clock.
            // The client counts down from this, but submit() re-derives the
            // truth, so freezing the JS timer gains nothing.
            'secondsLeft' => $this->secondsLeft($mockExam, $attempt),
        ]);
    }

    /** Periodic answer autosave so a crash or power cut doesn't cost the sitting. */
    public function autosave(Request $request, MockExam $mockExam)
    {
        $student = Auth::user();
        $this->assertRegistered($mockExam, $student->id);

        $attempt = MockExamAttempt::where('exam_id', $mockExam->id)->where('student_id', $student->id)->firstOrFail();
        if ($attempt->isSubmitted()) {
            return response()->json(['saved' => false, 'reason' => 'already_submitted'], 409);
        }

        $attempt->update(['answers' => $this->sanitiseAnswers($request->input('answers', []), $mockExam)]);

        return response()->json([
            'saved' => true,
            'seconds_left' => $this->secondsLeft($mockExam, $attempt),
        ]);
    }

    /**
     * Grade and close the sitting.
     *
     * Everything is scored against the item snapshot stored on the exam, never
     * against the live Test Bank, so a question edited after publish cannot
     * change a student's mark.
     */
    public function submit(Request $request, MockExam $mockExam)
    {
        $student = Auth::user();
        $this->assertRegistered($mockExam, $student->id);

        $attempt = MockExamAttempt::where('exam_id', $mockExam->id)->where('student_id', $student->id)->firstOrFail();
        if ($attempt->isSubmitted()) {
            return redirect()->route('mock-exams.result', $mockExam);
        }

        $answers = $this->sanitiseAnswers($request->input('answers', []), $mockExam);
        $items = $mockExam->items()->get();

        $score = 0;
        $totalPoints = 0;
        $topicTally = [];
        $answerResults = [];

        foreach ($items as $item) {
            $selected = $answers[(string) $item->id] ?? null;
            $isCorrect = $item->isCorrect($selected);
            $totalPoints += $item->points;
            if ($isCorrect) {
                $score += $item->points;
            }

            if ($item->topic_id) {
                $topicTally[$item->topic_id] ??= ['attempts' => 0, 'correct' => 0, 'trailing_wrong' => 0];
                $topicTally[$item->topic_id]['attempts']++;
                $topicTally[$item->topic_id]['correct'] += $isCorrect ? 1 : 0;
                // Trailing run of wrong answers, in the order answered - the "3 in a row" streak.
                $topicTally[$item->topic_id]['trailing_wrong'] = $isCorrect ? 0 : $topicTally[$item->topic_id]['trailing_wrong'] + 1;
            }

            if ($item->source_question_id) {
                $answerResults[] = [
                    'question_id' => $item->source_question_id,
                    'difficulty' => $item->difficulty,
                    'correct' => $isCorrect,
                ];
            }
        }

        // Same server-side reasoning as quiz_sessions.is_late: derived from
        // started_at against the exam's own window, never from client state.
        $isLate = now()->greaterThan($mockExam->endsAt());

        $attempt->update([
            'answers' => $answers,
            'score' => $score,
            'total_points' => $totalPoints,
            'percent' => $totalPoints > 0 ? round($score / $totalPoints * 100, 2) : 0,
            'submitted_at' => now(),
            'is_late' => $isLate,
            'status' => MockExamAttempt::STATUS_SUBMITTED,
        ]);

        // Analytics run after the grade is committed so a failure here can
        // never void a finished exam. No points are awarded: a graded exam is
        // not practice, and a 100-item paper would distort the leaderboard.
        try {
            app(PerformanceRecorder::class)->record($student->id, $topicTally);
            app(SpacedRepetitionScheduler::class)->recordAnswers($student->id, $answerResults);
            app(WeaknessDetector::class)->syncMany($student->id, array_keys($topicTally));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('mock-exams.result', $mockExam);
    }

    /** Graded results with a per-topic breakdown. */
    public function result(MockExam $mockExam)
    {
        $student = Auth::user();
        $this->assertRegistered($mockExam, $student->id);

        $attempt = MockExamAttempt::where('exam_id', $mockExam->id)->where('student_id', $student->id)->firstOrFail();
        abort_unless($attempt->isSubmitted(), 403, 'This exam has not been submitted yet.');

        $items = $mockExam->items()->get();
        $answers = (array) ($attempt->answers ?? []);

        return view('student.mock-exam-result', [
            'exam' => $mockExam->load('subject'),
            'subject' => $mockExam->subject,
            'theme' => self::theme($mockExam->subject?->code),
            'attempt' => $attempt,
            'items' => $items,
            'answers' => $answers,
            'byTopic' => $this->topicBreakdown($items, $answers),
        ]);
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function assertStudent(): void
    {
        abort_unless(Auth::user()?->isStudent(), 403, 'Mock exams are for student accounts.');
    }

    /**
     * A student may only see an exam whose day-code they actually redeemed,
     * and only once it is published.
     */
    private function assertRegistered(MockExam $exam, int $studentId): void
    {
        $this->assertStudent();
        abort_if(Auth::user()->hasAlumniAccess(), 403, 'Mock exams are locked for alumni accounts.');

        abort_unless(
            in_array($exam->status, [MockExam::STATUS_PUBLISHED, MockExam::STATUS_CLOSED], true)
                && $exam->event_id !== null
                && MockExamRegistration::where('event_id', $exam->event_id)->where('student_id', $studentId)->exists(),
            403,
            'You have not redeemed the code for this exam.'
        );
    }

    /** Every published exam on a day this student has redeemed. */
    private function registeredExams(int $studentId)
    {
        $eventIds = MockExamRegistration::where('student_id', $studentId)->pluck('event_id');
        if ($eventIds->isEmpty()) {
            return collect();
        }

        return MockExam::with('subject')
            ->whereIn('event_id', $eventIds)
            ->whereIn('status', [MockExam::STATUS_PUBLISHED, MockExam::STATUS_CLOSED])
            ->orderBy('scheduled_at')
            ->get();
    }

    private function attemptsFor(int $studentId, $examIds)
    {
        return MockExamAttempt::where('student_id', $studentId)
            ->whereIn('exam_id', $examIds)
            ->get()
            ->keyBy('exam_id');
    }

    /**
     * Seconds remaining, bounded by BOTH the student's own duration and the
     * exam's scheduled window - a student who starts late gets the remainder
     * of the sitting, not a fresh full clock.
     */
    private function secondsLeft(MockExam $exam, MockExamAttempt $attempt): int
    {
        $personalEnd = $attempt->started_at->copy()->addMinutes($exam->duration_minutes);
        $end = $personalEnd->lessThan($exam->endsAt()) ? $personalEnd : $exam->endsAt();

        return max(0, (int) now()->diffInSeconds($end, false));
    }

    /**
     * Keep only choices that actually exist on their item, so a tampered
     * payload can't inject an answer the paper never offered.
     *
     * @return array<string, string>
     */
    private function sanitiseAnswers($raw, MockExam $exam): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $valid = $exam->items()->get()->keyBy('id');
        $clean = [];

        foreach ($raw as $itemId => $label) {
            $item = $valid->get((int) $itemId);
            if (! $item || ! is_scalar($label)) {
                continue;
            }
            $label = (string) $label;
            if (collect($item->choices)->contains(fn ($c) => ($c['label'] ?? null) === $label)) {
                $clean[(string) $item->id] = $label;
            }
        }

        return $clean;
    }

    /** @return array<int, array<string, mixed>> */
    private function topicBreakdown($items, array $answers): array
    {
        $topicNames = DB::table('topics')
            ->whereIn('id', $items->pluck('topic_id')->filter()->unique())
            ->pluck('name', 'id');

        $rows = [];
        foreach ($items as $item) {
            $key = $item->topic_id ?: 0;
            $rows[$key] ??= [
                'topic' => $topicNames[$item->topic_id] ?? 'Uncategorised',
                'total' => 0,
                'correct' => 0,
            ];
            $rows[$key]['total']++;
            $rows[$key]['correct'] += $item->isCorrect($answers[(string) $item->id] ?? null) ? 1 : 0;
        }

        foreach ($rows as &$row) {
            $row['percent'] = $row['total'] > 0 ? round($row['correct'] / $row['total'] * 100) : 0;
        }

        // Weakest first - that's the list a student should act on.
        uasort($rows, fn ($a, $b) => $a['percent'] <=> $b['percent']);

        return array_values($rows);
    }
}
