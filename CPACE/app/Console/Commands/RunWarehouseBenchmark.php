<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\TestReport;
use App\Models\User;
use App\Services\TestReportParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Times a handful of the app's real heavy reporting/aggregate queries
 * (the "data warehouse" queries per the Super Admin Test Reports page —
 * chair analytics, faculty performance, test-bank coverage) against the
 * live database and uploads the result as a TestReport, same as a CI job
 * would via TestReportApiController. Runs standalone (no HTTP round trip,
 * no external tool needed) — schedule it in routes/console.php for a
 * recurring benchmark, or run it on demand.
 */
class RunWarehouseBenchmark extends Command
{
    protected $signature = 'benchmark:warehouse {--title=}';

    protected $description = 'Time the app\'s heaviest reporting queries and upload the result as a Test Report';

    public function handle(TestReportParser $parser): int
    {
        $queries = [
            'Faculty question-contribution aggregate' => fn () => DB::table('questions')
                ->join('topics', 'topics.id', '=', 'questions.topic_id')
                ->whereNotNull('questions.created_by')
                ->groupBy('questions.created_by')
                ->select('questions.created_by', DB::raw('COUNT(*) as total'))
                ->get(),

            'Student accuracy leaderboard' => fn () => DB::table('performance_records')
                ->join('users', 'users.id', '=', 'performance_records.student_id')
                ->where('performance_records.total_attempts', '>', 0)
                ->groupBy('performance_records.student_id')
                ->select(
                    'performance_records.student_id',
                    DB::raw('SUM(performance_records.correct_count) as correct'),
                    DB::raw('SUM(performance_records.total_attempts) as attempts')
                )
                ->get(),

            'Subject test-bank coverage' => fn () => DB::table('subjects')
                ->leftJoin('topics', 'topics.subject_id', '=', 'subjects.id')
                ->leftJoin('questions', 'questions.topic_id', '=', 'topics.id')
                ->groupBy('subjects.id')
                ->select('subjects.id', DB::raw('COUNT(questions.id) as question_count'))
                ->get(),

            'Quiz answer accuracy by topic' => fn () => DB::table('quiz_answers')
                ->join('questions', 'questions.id', '=', 'quiz_answers.question_id')
                ->join('topics', 'topics.id', '=', 'questions.topic_id')
                ->whereNotNull('quiz_answers.is_correct')
                ->groupBy('topics.id')
                ->select(
                    'topics.id',
                    DB::raw('COUNT(*) as answered'),
                    DB::raw('SUM(quiz_answers.is_correct) as correct')
                )
                ->get(),
        ];

        $results = [];
        foreach ($queries as $label => $runner) {
            $start = microtime(true);
            $rows = $runner();
            $durationMs = (int) round((microtime(true) - $start) * 1000);

            $results[] = ['query' => $label, 'rows' => $rows->count(), 'duration_ms' => $durationMs];
            $this->line(sprintf('%-45s %5dms  (%d rows)', $label, $durationMs, $rows->count()));
        }

        $payload = ['queries' => $results];
        $summary = $parser->summarize('warehouse', $payload);

        $uploader = User::where('role_id', Role::SUPER_ADMIN)->first();

        $report = TestReport::create([
            'type' => 'warehouse',
            'title' => $this->option('title') ?: 'Warehouse benchmark — ' . now()->format('M j, Y g:i A'),
            'summary' => $summary,
            'raw_payload' => json_encode($payload),
            'uploaded_by' => $uploader?->id,
            'created_at' => now(),
        ]);

        $this->info("Uploaded as Test Report #{$report->id} — total {$summary['total_duration_ms']}ms across " . count($results) . ' queries.');

        return self::SUCCESS;
    }
}
