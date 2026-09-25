<?php

namespace App\Http\Controllers;

use App\Models\MockExamAttempt;
use App\Models\MockExamProctorCapture;
use App\Models\MockExamProctorEvent;
use App\Support\ProctorCaptureRetention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Proctoring ingest and playback for a mock exam sitting.
 *
 * What is and isn't possible here is worth stating plainly, because the
 * feature is often assumed to do more than any browser allows: a web page
 * CANNOT silently screenshot the screen or open the camera. getUserMedia()
 * and getDisplayMedia() both require an explicit permission prompt and a user
 * gesture, the student chooses what to share, and the browser shows a
 * permanent sharing indicator. There is no bypass.
 *
 * So the design gates entry on granting both, captures frames from the
 * already-granted streams on a timer and on suspicious events, and treats
 * revocation mid-exam as a flag in its own right (camera_lost / screen_lost).
 *
 * Captures live on the PRIVATE disk and are served only through show(), which
 * re-checks that the viewer is the owning faculty or the Program Chair. They
 * are photographs of students' faces and screens; a guessable public URL would
 * be a serious problem.
 */
class MockExamProctorController extends Controller
{
    /** Cap on a single uploaded frame (KB). Frames are downscaled client-side. */
    private const MAX_CAPTURE_KB = 1536;

    /**
     * Record a behavioural flag. Posted by the student's own runner, so the
     * attempt must belong to the caller.
     */
    public function event(Request $request, MockExamAttempt $attempt)
    {
        $this->assertOwnLiveAttempt($attempt);

        $data = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', MockExamProctorEvent::TYPES)],
            'meta' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($attempt, $data) {
            MockExamProctorEvent::create([
                'attempt_id' => $attempt->id,
                'type' => $data['type'],
                'occurred_at' => now(),
                'meta' => $data['meta'] ?? null,
            ]);

            // Denormalised onto the attempt so the monitor can sort a roomful
            // of students by flag count without an aggregate per row.
            $attempt->increment('flag_count');
        });

        return response()->json(['recorded' => true, 'flags' => $attempt->fresh()->flag_count]);
    }

    /** Store one camera or screen frame. */
    public function capture(Request $request, MockExamAttempt $attempt)
    {
        $this->assertOwnLiveAttempt($attempt);

        $data = $request->validate([
            'kind' => ['required', 'string', 'in:' . implode(',', MockExamProctorCapture::KINDS)],
            'reason' => ['nullable', 'string', 'max:30'],
            'frame' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_CAPTURE_KB],
        ]);

        // One directory per exam per attempt keeps the purge command a simple
        // recursive delete rather than a per-file sweep.
        $dir = MockExamProctorCapture::ROOT . '/' . $attempt->exam_id . '/' . $attempt->id;
        $name = $data['kind'] . '-' . now()->format('His') . '-' . Str::lower(Str::random(6))
            . '.' . $request->file('frame')->extension();

        $path = $request->file('frame')->storeAs($dir, $name, 'local');

        $capture = MockExamProctorCapture::create([
            'attempt_id' => $attempt->id,
            'kind' => $data['kind'],
            'path' => $path,
            'captured_at' => now(),
            'reason' => $data['reason'] ?? 'interval',
        ]);

        return response()->json(['id' => $capture->id]);
    }

    /**
     * Serve one capture to an authorised viewer. Never a direct file URL: the
     * ownership check has to happen on every read.
     */
    public function show(MockExamProctorCapture $capture)
    {
        $capture->load('attempt.exam');
        $viewer = Auth::user();

        abort_unless(
            $viewer && $capture->attempt?->exam?->canBeViewedBy($viewer),
            403,
            'You are not authorised to view this recording.'
        );

        abort_unless(Storage::disk('local')->exists($capture->path), 404);

        return response()->file(Storage::disk('local')->path($capture->path));
    }

    /**
     * Delete the frames the faculty or Chair ticked, once they have decided the
     * case. The flag history stays. Refused while the student is still sitting,
     * so a running exam can't be blinded mid-way.
     */
    public function destroyCaptures(Request $request, MockExamAttempt $attempt)
    {
        $attempt->load('exam');

        abort_unless(
            Auth::user() && $attempt->exam?->canBeViewedBy(Auth::user()),
            403,
            'You are not authorised to delete these recordings.'
        );
        abort_unless($attempt->isSubmitted(), 409, 'This student is still sitting the exam.');

        $data = $request->validate([
            'capture_ids' => ['required', 'array', 'min:1'],
            'capture_ids.*' => ['integer'],
        ], [
            'capture_ids.required' => 'Tick at least one recording to delete.',
            'capture_ids.min' => 'Tick at least one recording to delete.',
        ]);

        $deleted = app(ProctorCaptureRetention::class)->deleteSelected($attempt, $data['capture_ids']);

        return back()->with('status', $deleted > 0
            ? "Deleted {$deleted} recording(s). The flag timeline was kept."
            : 'Those recordings were already gone.');
    }

    /**
     * The student may only write evidence against their own, still-running
     * attempt - so a finished attempt cannot be back-filled, and nobody can
     * post flags onto somebody else's sitting.
     */
    private function assertOwnLiveAttempt(MockExamAttempt $attempt): void
    {
        abort_unless($attempt->student_id === Auth::id(), 403, 'That is not your exam attempt.');
        abort_if($attempt->isSubmitted(), 409, 'This attempt has already been submitted.');
    }
}
