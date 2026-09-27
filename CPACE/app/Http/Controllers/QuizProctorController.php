<?php

namespace App\Http\Controllers;

use App\Models\FacultyQuizAttempt;
use App\Models\QuizProctorCapture;
use App\Models\QuizProctorEvent;
use App\Support\QuizProctorRetention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Proctoring ingest and playback for a monitored class quiz. Same rules as the
 * mock exam's (see MockExamProctorController for the browser limits): the
 * student posts flags and frames from streams they granted themselves, and a
 * frame is only ever served to the faculty member who owns the quiz.
 */
class QuizProctorController extends Controller
{
    private const MAX_CAPTURE_KB = 1536;

    public function event(Request $request, FacultyQuizAttempt $attempt)
    {
        $this->assertOwnLiveMonitoredAttempt($attempt);

        $data = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', QuizProctorEvent::TYPES)],
            'meta' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($attempt, $data) {
            QuizProctorEvent::create([
                'attempt_id' => $attempt->id,
                'type' => $data['type'],
                'occurred_at' => now(),
                'meta' => $data['meta'] ?? null,
            ]);
            $attempt->increment('flag_count');
        });

        return response()->json(['recorded' => true, 'flags' => $attempt->fresh()->flag_count]);
    }

    public function capture(Request $request, FacultyQuizAttempt $attempt)
    {
        $this->assertOwnLiveMonitoredAttempt($attempt);

        $data = $request->validate([
            'kind' => ['required', 'string', 'in:' . implode(',', QuizProctorCapture::KINDS)],
            'reason' => ['nullable', 'string', 'max:30'],
            'frame' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_CAPTURE_KB],
        ]);

        $dir = QuizProctorCapture::ROOT . '/' . $attempt->quiz_id . '/' . $attempt->id;
        $name = $data['kind'] . '-' . now()->format('His') . '-' . Str::lower(Str::random(6))
            . '.' . $request->file('frame')->extension();

        $capture = QuizProctorCapture::create([
            'attempt_id' => $attempt->id,
            'kind' => $data['kind'],
            'path' => $request->file('frame')->storeAs($dir, $name, 'local'),
            'captured_at' => now(),
            'reason' => $data['reason'] ?? 'interval',
        ]);

        return response()->json(['id' => $capture->id]);
    }

    /** Serve one frame to the quiz's owner. Checked on every read. */
    public function show(QuizProctorCapture $capture)
    {
        $capture->load('attempt.quiz');

        abort_unless(
            Auth::check() && $capture->attempt?->quiz?->faculty_id === Auth::id(),
            403,
            'You are not authorised to view this recording.'
        );
        abort_unless(Storage::disk('local')->exists($capture->path), 404);

        return response()->file(Storage::disk('local')->path($capture->path));
    }

    /** Owner deletes the frames they ticked, once the sitting is over. Flags stay. */
    public function destroy(Request $request, FacultyQuizAttempt $attempt)
    {
        $attempt->load('quiz');

        abort_unless(Auth::check() && $attempt->quiz?->faculty_id === Auth::id(), 403, 'You are not authorised to delete these recordings.');
        abort_unless($attempt->isSubmitted(), 409, 'This student is still taking the quiz.');

        $data = $request->validate([
            'capture_ids' => ['required', 'array', 'min:1'],
            'capture_ids.*' => ['integer'],
        ], [
            'capture_ids.required' => 'Tick at least one recording to delete.',
            'capture_ids.min' => 'Tick at least one recording to delete.',
        ]);

        $deleted = app(QuizProctorRetention::class)->deleteSelected($attempt, $data['capture_ids']);

        return back()->with('status', $deleted > 0
            ? "Deleted {$deleted} recording(s). The flag timeline was kept."
            : 'Those recordings were already gone.');
    }

    /**
     * A student writes evidence only against their own still-running attempt,
     * and only when the faculty turned monitoring on for that quiz.
     */
    private function assertOwnLiveMonitoredAttempt(FacultyQuizAttempt $attempt): void
    {
        $attempt->loadMissing('quiz');

        abort_unless($attempt->student_id === Auth::id(), 403, 'That is not your quiz attempt.');
        abort_unless($attempt->quiz?->monitor_enabled, 409, 'This quiz is not monitored.');
        abort_if($attempt->isSubmitted(), 409, 'This attempt has already been submitted.');
    }
}
