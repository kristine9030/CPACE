<?php

namespace App\Support;

use App\Models\MockExam;
use App\Models\MockExamAttempt;
use App\Models\MockExamProctorCapture;
use Illuminate\Support\Facades\Storage;

/**
 * What happens to a proctoring frame, and when.
 *
 * Frames are photographs of students, so the rule is to keep as little as
 * possible for as short as possible:
 *
 *  - a sitting with no flags keeps NOTHING - there is nothing to prove, so its
 *    frames are deleted the moment the student submits;
 *  - a flagged sitting keeps only the frames that back a flag (plus the opening
 *    frame, as a "who sat this" reference) - the routine timer frames go at
 *    submit;
 *  - whatever is left is swept RETENTION_DAYS after the exam, or sooner by hand
 *    once the faculty or Chair has decided the case.
 *
 * The flag rows themselves (mock_exam_proctor_events) are tiny text and are never
 * touched here; they remain the durable record.
 */
class ProctorCaptureRetention
{
    /**
     * Called when a student submits. Returns how many frames were discarded.
     */
    public function afterSubmit(MockExamAttempt $attempt): int
    {
        $attempt->refresh();

        if ($attempt->flag_count <= 0) {
            return $this->deleteAllFor($attempt);
        }

        return $this->discard(
            $attempt->captures()->where('reason', MockExamProctorCapture::REASON_INTERVAL)->get()
        );
    }

    /** Remove every frame for one attempt (the manual "delete recordings" action). */
    public function deleteAllFor(MockExamAttempt $attempt): int
    {
        $deleted = $this->discard($attempt->captures()->get());

        $dir = MockExamProctorCapture::ROOT . '/' . $attempt->exam_id . '/' . $attempt->id;
        if (Storage::disk('local')->exists($dir) && Storage::disk('local')->allFiles($dir) === []) {
            Storage::disk('local')->deleteDirectory($dir);
        }

        return $deleted;
    }

    /**
     * Frame count and disk size for one exam, for the "storage" line on the monitor.
     *
     * @return array{count: int, bytes: int}
     */
    public function usageFor(MockExam $exam): array
    {
        $captures = MockExamProctorCapture::whereIn(
            'attempt_id',
            MockExamAttempt::where('exam_id', $exam->id)->select('id')
        )->get();

        $bytes = 0;
        foreach ($captures as $capture) {
            if (Storage::disk('local')->exists($capture->path)) {
                $bytes += Storage::disk('local')->size($capture->path);
            }
        }

        return ['count' => $captures->count(), 'bytes' => $bytes];
    }

    /** "12.4 MB" style label. */
    public static function humanSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024) . ' KB';
        }

        return round($bytes / 1048576, 1) . ' MB';
    }

    /** @param \Illuminate\Support\Collection<int, MockExamProctorCapture> $captures */
    private function discard($captures): int
    {
        $deleted = 0;
        foreach ($captures as $capture) {
            Storage::disk('local')->delete($capture->path);
            $capture->delete();
            $deleted++;
        }

        return $deleted;
    }
}
