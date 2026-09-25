<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Concerns\EditsMockExams;
use App\Http\Controllers\Concerns\SubjectTheme;
use App\Http\Controllers\Controller;
use App\Models\MockExam;
use App\Models\MockExamAttempt;
use App\Models\MockExamAudit;
use App\Models\MockExamEvent;
use App\Models\MockExamProctorCapture;
use App\Models\Subject;
use App\Models\Topic;
use App\Support\MockExamAuditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Program Chair side of the mock exam workflow: review what faculty built,
 * edit it, send it back or publish it, and monitor the sitting.
 *
 * Publishing is the one-way door. It resolves (or creates) the day's
 * MockExamEvent so every subject sitting on that date shares one redeem code,
 * and from then on MockExam::isEditable() returns false for everybody -
 * including the Chair - so a paper students are about to sit cannot move
 * underneath them.
 *
 * monitor()/monitorFeed() are also routed for faculty, who see the sitting for
 * their own assigned subjects; MockExam::canBeViewedBy() is the shared gate.
 */
class MockExamReviewController extends Controller
{
    use EditsMockExams, SubjectTheme;

    /** Folder view: one folder per subject, badged with what's waiting. */
    public function index()
    {
        $exams = MockExam::selectRaw('subject_id, status, COUNT(*) as total')
            ->groupBy('subject_id', 'status')
            ->get()
            ->groupBy('subject_id');

        $cards = Subject::where('is_active', true)->orderBy('id')->get()->map(function (Subject $subject) use ($exams) {
            $group = $exams->get($subject->id, collect());
            $count = fn (string $status) => (int) ($group->firstWhere('status', $status)->total ?? 0);

            return [
                'subject' => $subject,
                'icon' => self::subjectIcon($subject->code),
                'theme' => self::theme($subject->code),
                'for_review' => $count(MockExam::STATUS_FOR_REVIEW),
                'draft' => $count(MockExam::STATUS_DRAFT),
                'published' => $count(MockExam::STATUS_PUBLISHED),
                'closed' => $count(MockExam::STATUS_CLOSED),
                'total' => (int) $group->sum('total'),
            ];
        })->values();

        return view('chair.mock-exams', [
            'cards' => $cards,
            'pendingTotal' => $cards->sum('for_review'),
        ]);
    }

    /** One subject's folder. */
    public function subject(Subject $subject)
    {
        $exams = MockExam::with(['creator', 'publisher', 'event'])
            ->withCount([
                'items',
                'attempts',
                'attempts as submitted_count' => fn ($q) => $q->whereNotNull('submitted_at'),
            ])
            ->where('subject_id', $subject->id)
            // Anything waiting on the Chair floats to the top.
            ->orderByRaw("CASE status WHEN 'for_review' THEN 0 WHEN 'published' THEN 1 WHEN 'draft' THEN 2 ELSE 3 END")
            ->orderByDesc('scheduled_at')
            ->orderByDesc('id')
            ->get();

        return view('chair.mock-exam-subject', [
            'subject' => $subject,
            'icon' => self::subjectIcon($subject->code),
            'theme' => self::theme($subject->code),
            'exams' => $exams,
        ]);
    }

    /** The review screen: settings, topics, every item, and the audit trail. */
    public function review(MockExam $mockExam)
    {
        $mockExam->load(['subject', 'items', 'topics', 'creator', 'publisher', 'event', 'audits.user']);

        return view('chair.mock-exam-review', [
            'exam' => $mockExam,
            'subject' => $mockExam->subject,
            'icon' => self::subjectIcon($mockExam->subject?->code),
            'theme' => self::theme($mockExam->subject?->code),
            'topicTree' => $this->topicsFor($mockExam->subject_id),
            'selectedTopics' => $mockExam->topics->pluck('id')->all(),
            'readOnly' => ! $mockExam->isEditable(),
        ]);
    }

    /** The Chair's edits go through exactly the same write path as faculty. */
    public function update(Request $request, MockExam $mockExam)
    {
        $this->assertEditable($mockExam);

        $this->saveMockExam($request, $mockExam, Auth::user());

        return redirect()->route('chair.mock-exams.review', $mockExam)
            ->with('status', 'Changes saved and recorded in the audit trail.');
    }

    /** Send it back to the faculty with a note rather than publish-or-nothing. */
    public function returnForRevision(Request $request, MockExam $mockExam)
    {
        $this->assertEditable($mockExam);

        $data = $request->validate([
            'review_note' => ['required', 'string', 'max:2000'],
        ], [
            'review_note.required' => 'Tell the faculty what needs changing.',
        ]);

        $mockExam->update([
            'status' => MockExam::STATUS_DRAFT,
            'review_note' => trim($data['review_note']),
            'reviewed_by' => Auth::id(),
            'submitted_for_review_at' => null,
        ]);

        MockExamAuditor::record($mockExam, Auth::user(), MockExamAudit::ACTION_RETURNED, trim($data['review_note']));
        $this->notifySubjectFaculty($mockExam, 'Mock exam returned for revision', 'needs changes before it can be published.');

        return redirect()->route('chair.mock-exams.subject', $mockExam->subject_id)
            ->with('status', 'Returned to the faculty for revision.');
    }

    /**
     * Publish: the one-way door. Resolves the day's event (creating the redeem
     * code if this is the first exam published for that date) and freezes the
     * paper.
     */
    public function publish(MockExam $mockExam)
    {
        $this->assertEditable($mockExam);
        $this->assertExamComplete($mockExam);

        DB::transaction(function () use ($mockExam) {
            $event = MockExamEvent::forDate($mockExam->scheduled_at, Auth::id());

            $mockExam->update([
                'event_id' => $event->id,
                'status' => MockExam::STATUS_PUBLISHED,
                'published_by' => Auth::id(),
                'published_at' => now(),
                'reviewed_by' => Auth::id(),
                'review_note' => null,
            ]);
        });

        $mockExam->refresh()->load('event');

        MockExamAuditor::record(
            $mockExam,
            Auth::user(),
            MockExamAudit::ACTION_PUBLISHED,
            'Code ' . $mockExam->event->access_code . ' · ' . $mockExam->total_items . ' questions'
        );

        $this->notifySubjectFaculty($mockExam, 'Mock exam published', 'is now published. Code: ' . $mockExam->event->access_code);

        return redirect()->route('chair.mock-exams.subject', $mockExam->subject_id)
            ->with('status', 'Published. Redeem code: ' . $mockExam->event->access_code);
    }

    /** Close a sitting early; in-progress attempts are left to finish. */
    public function close(MockExam $mockExam)
    {
        if (! $mockExam->isPublished()) {
            return back()->withErrors(['exam' => 'Only a published exam can be closed.']);
        }

        $mockExam->update(['status' => MockExam::STATUS_CLOSED, 'closed_at' => now()]);
        MockExamAuditor::record($mockExam, Auth::user(), MockExamAudit::ACTION_CLOSED);

        return back()->with('status', 'Mock exam closed.');
    }

    // ── monitoring (shared with faculty) ─────────────────────────────────────

    /**
     * Live monitor for one sitting. Rendered once; the numbers, snapshot grid
     * and flag list refresh from monitorFeed() on a poll - this app has no
     * broadcast infrastructure, and polling is more than adequate for a room
     * of students.
     */
    public function monitor(MockExam $mockExam)
    {
        $this->assertCanMonitor($mockExam);

        $mockExam->load(['subject', 'event']);

        return view('chair.mock-exam-monitor', [
            'exam' => $mockExam,
            'subject' => $mockExam->subject,
            'theme' => self::theme($mockExam->subject?->code),
            'isChair' => Auth::user()->isChair(),
            'registered' => $mockExam->event
                ? $mockExam->event->registrations()->count()
                : 0,
            'storage' => app(\App\Support\ProctorCaptureRetention::class)->usageFor($mockExam),
        ]);
    }

    /** JSON behind the monitor's poll. */
    public function monitorFeed(MockExam $mockExam)
    {
        $this->assertCanMonitor($mockExam);

        $attempts = MockExamAttempt::with('student:id,first_name,last_name')
            ->where('exam_id', $mockExam->id)
            ->get();

        // One query for the newest camera frame per attempt, rather than an
        // N+1 across a roomful of students.
        $latest = MockExamProctorCapture::whereIn('attempt_id', $attempts->pluck('id'))
            ->where('kind', MockExamProctorCapture::KIND_CAMERA)
            ->orderByDesc('captured_at')
            ->get()
            ->unique('attempt_id')
            ->keyBy('attempt_id');

        $rows = $attempts->map(function (MockExamAttempt $attempt) use ($latest) {
            $capture = $latest->get($attempt->id);

            return [
                'attempt_id' => $attempt->id,
                'student' => trim($attempt->student?->first_name . ' ' . $attempt->student?->last_name),
                'status' => $attempt->status,
                'flags' => $attempt->flag_count,
                'percent' => $attempt->isSubmitted() ? (float) $attempt->percent : null,
                'started_at' => $attempt->started_at?->toIso8601String(),
                'submitted_at' => $attempt->submitted_at?->toIso8601String(),
                'answered' => is_array($attempt->answers) ? count(array_filter($attempt->answers)) : 0,
                'camera' => $capture ? route('mock-exams.capture', $capture) : null,
                'camera_at' => $capture?->captured_at?->toIso8601String(),
                'detail' => Auth::user()->isChair()
                    ? route('chair.mock-exams.attempt', $attempt)
                    : route('faculty.mock-exams.attempt', $attempt),
            ];
        })->sortByDesc('flags')->values();

        return response()->json([
            'exam' => [
                'status' => $mockExam->status,
                'ends_at' => $mockExam->scheduled_at?->copy()->addMinutes($mockExam->duration_minutes)->toIso8601String(),
                'window' => $mockExam->window(),
            ],
            'kpis' => [
                'registered' => $mockExam->event?->registrations()->count() ?? 0,
                'started' => $attempts->count(),
                'submitted' => $attempts->where('status', MockExamAttempt::STATUS_SUBMITTED)->count(),
                'flagged' => $attempts->where('flag_count', '>', 0)->count(),
            ],
            'students' => $rows,
        ]);
    }

    /** One student's sitting: their flag timeline and every capture taken. */
    public function attempt(MockExamAttempt $attempt)
    {
        $attempt->load(['exam.subject', 'student', 'proctorEvents', 'captures']);
        $this->assertCanMonitor($attempt->exam);

        return view('chair.mock-exam-attempt', [
            'attempt' => $attempt,
            'exam' => $attempt->exam,
            'theme' => self::theme($attempt->exam->subject?->code),
            'items' => $attempt->exam->items()->get(),
            'isChair' => Auth::user()->isChair(),
        ]);
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function assertEditable(MockExam $exam): void
    {
        if (! $exam->isEditable()) {
            abort(403, 'This mock exam has been published and can no longer be edited.');
        }
    }

    /**
     * The Chair monitors anything; a faculty member only the subjects they are
     * assigned to. Captures are photographs of students, so this is checked on
     * every monitoring entry point rather than assumed from the route prefix.
     */
    private function assertCanMonitor(MockExam $exam): void
    {
        abort_unless($exam->canBeViewedBy(Auth::user()), 403, 'You are not assigned to that subject.');
    }

    private function topicsFor(int $subjectId)
    {
        $flat = Topic::where('subject_id', $subjectId)
            ->where('is_active', true)
            ->withCount(['questions as bank_count' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Topic::buildTree($flat);
    }

    /** Tell everyone assigned to the subject what the Chair decided. */
    private function notifySubjectFaculty(MockExam $exam, string $title, string $tail): void
    {
        $facultyIds = DB::table('faculty_subjects')
            ->where('subject_id', $exam->subject_id)
            ->pluck('faculty_id')
            ->unique();

        if ($facultyIds->isEmpty()) {
            return;
        }

        $message = 'The ' . ($exam->subject?->code ?? 'subject') . ' mock exam for '
            . $exam->scheduled_at?->format('M j, Y') . ' ' . $tail;

        DB::table('notifications')->insert($facultyIds->map(fn ($id) => [
            'recipient_id' => $id,
            'type' => 'mock_exam_review',
            'title' => $title,
            'message' => $message,
            'is_read' => 0,
            'reference_type' => 'mock_exam',
            'reference_id' => $exam->id,
            'created_at' => now(),
        ])->all());
    }
}
