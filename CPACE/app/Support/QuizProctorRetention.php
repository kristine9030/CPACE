<?php

namespace App\Support;

use App\Models\FacultyQuizAttempt;
use App\Models\QuizProctorCapture;
use Illuminate\Support\Facades\Storage;

/**
 * Same keep-as-little-as-possible rule as the mock exam's
 * ProctorCaptureRetention, for a monitored class quiz: a clean sitting keeps
 * only its opening camera photo, a flagged one keeps the frames behind its
 * flags, and whatever remains is swept after QuizProctorCapture::RETENTION_DAYS.
 */
class QuizProctorRetention
{
    /** Called when a student submits. */
    public function afterSubmit(FacultyQuizAttempt $attempt): int
    {
        $attempt->refresh();

        if ($attempt->flag_count <= 0) {
            return $this->discard(
                $attempt->captures()->get()->reject(fn (QuizProctorCapture $c) => $this->isOpeningPhoto($c))
            );
        }

        return $this->discard(
            $attempt->captures()->where('reason', QuizProctorCapture::REASON_INTERVAL)->get()
        );
    }

    /** @param array<int, int|string> $ids ids belonging to other attempts are ignored */
    public function deleteSelected(FacultyQuizAttempt $attempt, array $ids): int
    {
        $deleted = $this->discard($attempt->captures()->whereIn('id', $ids)->get());
        $this->tidy($attempt);

        return $deleted;
    }

    /** Delete every frame older than the retention window. Returns how many. */
    public function purgeOlderThan(int $days): int
    {
        return $this->discard(QuizProctorCapture::where('captured_at', '<', now()->subDays($days))->get());
    }

    private function tidy(FacultyQuizAttempt $attempt): void
    {
        $dir = QuizProctorCapture::ROOT . '/' . $attempt->quiz_id . '/' . $attempt->id;
        if (Storage::disk('local')->exists($dir) && Storage::disk('local')->allFiles($dir) === []) {
            Storage::disk('local')->deleteDirectory($dir);
        }
    }

    private function isOpeningPhoto(QuizProctorCapture $capture): bool
    {
        return $capture->kind === QuizProctorCapture::KIND_CAMERA
            && $capture->reason === QuizProctorCapture::REASON_START;
    }

    /** @param \Illuminate\Support\Collection<int, QuizProctorCapture> $captures */
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
