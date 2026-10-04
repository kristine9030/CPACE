<?php

namespace App\Console\Commands;

use App\Services\WeaknessDetector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Scores the real WeaknessDetector::evaluate() against a 120-scenario ground
 * truth dataset (20 per CPALE subject), per Chapter 3's Instrumentation
 * section and Chapter 4 Table XI.
 *
 * Ground truth labels are computed by an independent closure in this command
 * (expectedLabel()) that re-states the Scope's rule from scratch, rather than
 * by calling WeaknessDetector itself — the same "independent derivation"
 * principle already used for the SM-2 verification in EvaluationController.
 * Any mismatch therefore reflects either a real implementation bug or a
 * genuine PHP floating-point edge case (e.g. 3/5 and 9/15 both equal 0.60
 * mathematically but may not compare bit-for-bit identically as floats),
 * which is exactly the kind of boundary condition this dataset is designed
 * to surface.
 *
 * The same 20-scenario template is reused for all six subjects (only the
 * subject label differs) so that cross-subject comparison in Table XI is
 * meaningful rather than coincidental — every subject gets identical
 * coverage of the clearly-weak, clearly-strong, and boundary cases.
 */
class EvaluateWeaknessDetectionGroundTruth extends Command
{
    protected $signature = 'evaluate:weakness-detection';

    protected $description = 'Score WeaknessDetector against the 120-scenario ground truth dataset (Chapter 4 Table XI)';

    /** @var array<string,string> */
    private const SUBJECTS = [
        'FAR'  => 'Financial Accounting and Reporting',
        'AFAR' => 'Advanced Financial Accounting and Reporting',
        'MS'   => 'Management Services',
        'TAX'  => 'Taxation',
        'AUD'  => 'Auditing',
        'RFBT' => 'Regulatory Framework for Business Transactions',
    ];

    /**
     * [total_attempts, correct_count, consecutive_wrong, category]
     * 20 rows, reused per subject. See class docblock for rationale.
     */
    private const TEMPLATE = [
        [10, 2, 0, 'clearly_weak'],
        [9, 2, 1, 'clearly_weak'],
        [12, 3, 0, 'clearly_weak'],
        [8, 2, 2, 'clearly_weak'],
        [11, 3, 0, 'clearly_weak'],
        [8, 7, 0, 'clearly_strong'],
        [10, 9, 0, 'clearly_strong'],
        [9, 8, 1, 'clearly_strong'],
        [12, 11, 0, 'clearly_strong'],
        [15, 13, 0, 'clearly_strong'],
        [5, 2, 1, 'borderline_under_60'],
        [5, 3, 0, 'boundary_exactly_60_frac_3_5'],
        [20, 11, 0, 'borderline_just_under_60'],
        [20, 13, 0, 'borderline_just_over_60'],
        [15, 9, 0, 'boundary_exactly_60_frac_9_15'],
        [4, 1, 0, 'below_min_attempts_floor'],
        [3, 0, 2, 'below_min_attempts_and_streak'],
        [10, 8, 3, 'streak_overrides_high_accuracy'],
        [12, 10, 2, 'streak_near_miss'],
        [6, 3, 3, 'streak_triggers_regardless_of_attempts'],
    ];

    public function handle(): int
    {
        $scenarios = $this->buildScenarios();
        $detector = new WeaknessDetector();

        $perSubject = [];
        $overall = ['tp' => 0, 'fp' => 0, 'fn' => 0, 'tn' => 0];
        $rows = [];

        foreach ($scenarios as $scenario) {
            $expected = $this->expectedLabel($scenario['total_attempts'], $scenario['correct_count'], $scenario['consecutive_wrong']);

            $stub = (object) [
                'total_attempts' => $scenario['total_attempts'],
                'correct_count' => $scenario['correct_count'],
                'consecutive_wrong' => $scenario['consecutive_wrong'],
            ];
            [$actual] = $detector->evaluate($stub);

            $subject = $scenario['subject'];
            $perSubject[$subject] ??= ['tp' => 0, 'fp' => 0, 'fn' => 0, 'tn' => 0];

            $bucket = match (true) {
                $actual && $expected => 'tp',
                $actual && ! $expected => 'fp',
                ! $actual && $expected => 'fn',
                default => 'tn',
            };

            $perSubject[$subject][$bucket]++;
            $overall[$bucket]++;

            $rows[] = $scenario + [
                'expected_weak' => $expected ? 'weak' : 'not_weak',
                'actual_weak' => $actual ? 'weak' : 'not_weak',
                'match' => $bucket === 'tp' || $bucket === 'tn' ? 'match' : 'MISMATCH',
            ];
        }

        $table = [];
        foreach (self::SUBJECTS as $code => $label) {
            $m = $perSubject[$code];
            [$p, $r, $f1] = $this->metrics($m['tp'], $m['fp'], $m['fn']);
            $table[] = [$code, 20, $m['tp'], $m['fp'], $m['fn'], $p, $r, $f1];
        }
        [$op, $or, $of1] = $this->metrics($overall['tp'], $overall['fp'], $overall['fn']);
        $table[] = ['Overall', 120, $overall['tp'], $overall['fp'], $overall['fn'], $op, $or, $of1];

        $this->table(['Subject', 'Scenarios', 'TP', 'FP', 'FN', 'Precision', 'Recall', 'F1-Score'], $table);

        $mismatches = array_filter($rows, fn ($r) => $r['match'] === 'MISMATCH');
        if (count($mismatches) > 0) {
            $this->warn(count($mismatches).' mismatch(es) found — these are the ones worth looking at:');
            $this->table(
                ['scenario_id', 'attempts', 'correct', 'streak', 'expected', 'actual'],
                array_map(fn ($r) => [$r['scenario_id'], $r['total_attempts'], $r['correct_count'], $r['consecutive_wrong'], $r['expected_weak'], $r['actual_weak']], $mismatches)
            );
        } else {
            $this->info('No mismatches — WeaknessDetector agrees with the independently-derived ground truth on all 120 scenarios.');
        }

        Storage::put('evaluation/weakness_detection_results.json', json_encode([
            'per_subject' => $table,
            'overall' => ['precision' => $op, 'recall' => $or, 'f1' => $of1, 'tp' => $overall['tp'], 'fp' => $overall['fp'], 'fn' => $overall['fn']],
            'mismatches' => array_values($mismatches),
            'generated_at' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT));

        $csv = "scenario_id,subject,total_attempts,correct_count,consecutive_wrong,category,expected_weak,actual_weak,match\n";
        foreach ($rows as $r) {
            $csv .= "{$r['scenario_id']},{$r['subject']},{$r['total_attempts']},{$r['correct_count']},{$r['consecutive_wrong']},{$r['category']},{$r['expected_weak']},{$r['actual_weak']},{$r['match']}\n";
        }
        Storage::put('evaluation/weakness_detection_scenarios.csv', $csv);

        $this->info('Written to storage/app/evaluation/weakness_detection_results.json and weakness_detection_scenarios.csv');

        return self::SUCCESS;
    }

    private function buildScenarios(): array
    {
        $scenarios = [];
        foreach (self::SUBJECTS as $code => $label) {
            foreach (self::TEMPLATE as $i => [$attempts, $correct, $streak, $category]) {
                $scenarios[] = [
                    'scenario_id' => sprintf('%s-%02d', $code, $i + 1),
                    'subject' => $code,
                    'subject_label' => $label,
                    'total_attempts' => $attempts,
                    'correct_count' => $correct,
                    'consecutive_wrong' => $streak,
                    'category' => $category,
                ];
            }
        }

        return $scenarios;
    }

    /** Independently-derived ground truth label — mirrors the Scope's written rule, not the implementation. */
    private function expectedLabel(int $attempts, int $correct, int $wrongStreak): bool
    {
        if ($wrongStreak >= 3) {
            return true;
        }

        $accuracy = $attempts > 0 ? $correct / $attempts : 0.0;

        return $attempts >= 5 && $accuracy < 0.60;
    }

    private function metrics(int $tp, int $fp, int $fn): array
    {
        $precision = ($tp + $fp) > 0 ? round($tp / ($tp + $fp), 4) : null;
        $recall = ($tp + $fn) > 0 ? round($tp / ($tp + $fn), 4) : null;
        $f1 = ($precision !== null && $recall !== null && ($precision + $recall) > 0)
            ? round(2 * $precision * $recall / ($precision + $recall), 4)
            : null;

        return [$precision, $recall, $f1];
    }
}
