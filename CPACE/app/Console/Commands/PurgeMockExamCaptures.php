<?php

namespace App\Console\Commands;

use App\Models\MockExam;
use App\Models\MockExamProctorCapture;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sweeps proctoring frames for exams that finished long enough ago.
 *
 * A three-hour sitting is roughly 8 MB of JPEG per student, so a 40-student
 * exam day is ~330 MB - enough to fill shared hosting within a term. The
 * behavioural flags (mock_exam_proctor_events) are the durable evidence and
 * are deliberately left alone; only the images go.
 */
class PurgeMockExamCaptures extends Command
{
    protected $signature = 'mock-exam:purge-captures
                            {--days=30 : Delete captures for exams that ended more than this many days ago}
                            {--dry-run : Report what would be deleted without deleting it}';

    protected $description = 'Delete stored mock exam camera/screen frames for exams that ended more than N days ago';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $examIds = MockExam::whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', $cutoff)
            ->pluck('id');

        if ($examIds->isEmpty()) {
            $this->info('No mock exams older than ' . $days . ' days. Nothing to purge.');

            return self::SUCCESS;
        }

        $captures = MockExamProctorCapture::whereIn(
            'attempt_id',
            \App\Models\MockExamAttempt::whereIn('exam_id', $examIds)->select('id')
        );

        $count = (clone $captures)->count();
        if ($count === 0) {
            $this->info('No captures to purge.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info("Would delete {$count} capture(s) across {$examIds->count()} exam(s).");

            return self::SUCCESS;
        }

        $deleted = 0;
        // Chunked so a term's worth of frames doesn't have to fit in memory.
        (clone $captures)->chunkById(500, function ($rows) use (&$deleted) {
            foreach ($rows as $capture) {
                if (Storage::disk('local')->exists($capture->path)) {
                    Storage::disk('local')->delete($capture->path);
                }
                $capture->delete();
                $deleted++;
            }
        });

        // Tidy the now-empty per-exam directories.
        foreach ($examIds as $examId) {
            $dir = MockExamProctorCapture::ROOT . '/' . $examId;
            if (Storage::disk('local')->exists($dir) && Storage::disk('local')->allFiles($dir) === []) {
                Storage::disk('local')->deleteDirectory($dir);
            }
        }

        $this->info("Purged {$deleted} capture(s). Flag history was kept.");

        return self::SUCCESS;
    }
}
