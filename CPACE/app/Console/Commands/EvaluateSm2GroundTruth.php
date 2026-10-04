<?php

namespace App\Console\Commands;

use App\Services\SpacedRepetitionScheduler;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Scores the real SpacedRepetitionScheduler::next() against a generated set
 * of test cases covering every rule in the SM-2 spec (Chapter 3 Instrumentation,
 * Chapter 4 Table XII): I(1)=1, I(2)=6, I(n)=I(n-1)*EF for n>2, the EF update
 * formula, the 1.3 EF floor, and lapse handling (q<3 resets the chain).
 *
 * Expected values are computed by expectedStep() below, which re-states the
 * SM-2 formulas from scratch rather than calling the scheduler — the same
 * independent-derivation principle the existing EvaluationController::
 * verifySm2() already uses for its 5 hand-picked cases. This command
 * generates a much larger, systematically varied set and groups results by
 * which rule each case is primarily exercising, matching Table XII's rows.
 */
class EvaluateSm2GroundTruth extends Command
{
    protected $signature = 'evaluate:sm2';

    protected $description = 'Score SpacedRepetitionScheduler against a generated SM-2 test case set (Chapter 4 Table XII)';

    public function handle(): int
    {
        $scheduler = new SpacedRepetitionScheduler();
        $anchor = Carbon::create(2026, 1, 1);

        $cases = $this->buildCases();

        $byCategory = [];
        $rows = [];

        foreach ($cases as $case) {
            $state = ['repetition_num' => $case['prior_rep'], 'ease_factor' => $case['prior_ef'], 'interval_days' => $case['prior_interval']];
            $actual = $scheduler->next($state, $case['quality'], $anchor);
            $expected = $this->expectedStep($state, $case['quality']);

            $match = $actual['repetition_num'] === $expected['repetition_num']
                && abs($actual['ease_factor'] - $expected['ease_factor']) < 0.005
                && $actual['interval_days'] === $expected['interval_days'];

            $byCategory[$case['category']] ??= ['cases' => 0, 'matches' => 0];
            $byCategory[$case['category']]['cases']++;
            $byCategory[$case['category']]['matches'] += $match ? 1 : 0;

            $rows[] = $case + [
                'expected' => $expected,
                'actual' => ['repetition_num' => $actual['repetition_num'], 'ease_factor' => $actual['ease_factor'], 'interval_days' => $actual['interval_days']],
                'match' => $match,
            ];
        }

        $labels = [
            'first_repetition' => 'First repetition, I(1) = 1 day',
            'second_repetition' => 'Second repetition, I(2) = 6 days',
            'later_repetitions' => 'Later repetitions, I(n) = I(n-1) x EF',
            'ease_factor_update' => "Ease factor update, EF'",
            'ease_factor_floor' => 'Ease factor lower bound at 1.3',
            'lapse_handling' => 'Lapse handling, q < 3',
        ];

        $table = [];
        $totalCases = 0;
        $totalMatches = 0;
        foreach ($labels as $key => $label) {
            $c = $byCategory[$key]['cases'] ?? 0;
            $m = $byCategory[$key]['matches'] ?? 0;
            $mismatches = $c - $m;
            $rate = $c > 0 ? round($m / $c * 100, 2) : null;
            $table[] = [$label, $c, $m, $mismatches, $rate !== null ? "{$rate}%" : '—'];
            $totalCases += $c;
            $totalMatches += $m;
        }
        $overallRate = $totalCases > 0 ? round($totalMatches / $totalCases * 100, 2) : null;
        $table[] = ['Overall', $totalCases, $totalMatches, $totalCases - $totalMatches, "{$overallRate}%"];

        $this->table(['Test Category', 'Test Cases', 'Exact Matches', 'Mismatches', 'Correctness Rate'], $table);

        $mismatchRows = array_filter($rows, fn ($r) => ! $r['match']);
        if (count($mismatchRows) > 0) {
            $this->warn(count($mismatchRows).' mismatch(es) — worth inspecting:');
            $this->table(
                ['category', 'prior_rep', 'prior_ef', 'prior_interval', 'quality', 'expected', 'actual'],
                array_map(fn ($r) => [
                    $r['category'], $r['prior_rep'], $r['prior_ef'], $r['prior_interval'], $r['quality'],
                    json_encode($r['expected']), json_encode($r['actual']),
                ], $mismatchRows)
            );
        } else {
            $this->info("No mismatches — SpacedRepetitionScheduler agrees with the independently-derived SM-2 formulas on all {$totalCases} cases.");
        }

        Storage::put('evaluation/sm2_results.json', json_encode([
            'by_category' => $table,
            'total_cases' => $totalCases,
            'total_matches' => $totalMatches,
            'overall_correctness_rate' => $overallRate,
            'mismatches' => array_values($mismatchRows),
            'generated_at' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT));

        $csv = "category,prior_rep,prior_ef,prior_interval,quality,expected_rep,expected_ef,expected_interval,actual_rep,actual_ef,actual_interval,match\n";
        foreach ($rows as $r) {
            $csv .= "{$r['category']},{$r['prior_rep']},{$r['prior_ef']},{$r['prior_interval']},{$r['quality']},"
                ."{$r['expected']['repetition_num']},{$r['expected']['ease_factor']},{$r['expected']['interval_days']},"
                ."{$r['actual']['repetition_num']},{$r['actual']['ease_factor']},{$r['actual']['interval_days']},"
                .($r['match'] ? 'match' : 'MISMATCH')."\n";
        }
        Storage::put('evaluation/sm2_test_cases.csv', $csv);

        $this->info('Written to storage/app/evaluation/sm2_results.json and sm2_test_cases.csv');

        return self::SUCCESS;
    }

    private function buildCases(): array
    {
        $cases = [];
        $efs = [1.3, 1.5, 1.8, 2.0, 2.2, 2.5, 2.8, 3.0, 3.3, 3.5];

        // First repetition: rep 0 -> 1, any passing quality, I(1) must be 1 regardless of EF.
        foreach ($efs as $ef) {
            foreach ([3, 4, 5] as $q) {
                $cases[] = ['category' => 'first_repetition', 'prior_rep' => 0, 'prior_ef' => $ef, 'prior_interval' => 0, 'quality' => $q];
            }
        }

        // Second repetition: rep 1 -> 2, I(2) must be 6 regardless of EF.
        foreach ($efs as $ef) {
            foreach ([3, 4, 5] as $q) {
                $cases[] = ['category' => 'second_repetition', 'prior_rep' => 1, 'prior_ef' => $ef, 'prior_interval' => 1, 'quality' => $q];
            }
        }

        // Later repetitions: rep >= 2, I(n) = I(n-1) * EF.
        foreach ([2, 3, 4, 5] as $rep) {
            foreach ([6, 12, 18, 24, 30] as $interval) {
                foreach ([3, 4, 5] as $q) {
                    $cases[] = ['category' => 'later_repetitions', 'prior_rep' => $rep, 'prior_ef' => 2.5, 'prior_interval' => $interval, 'quality' => $q];
                }
            }
        }

        // Ease factor update formula across the full quality range, independent of rep stage.
        foreach ([1.3, 1.8, 2.3, 2.8, 3.3] as $ef) {
            foreach ([0, 1, 2, 3, 4, 5] as $q) {
                $cases[] = ['category' => 'ease_factor_update', 'prior_rep' => 3, 'prior_ef' => $ef, 'prior_interval' => 10, 'quality' => $q];
            }
        }

        // Ease factor floor: starting near/at 1.3 with low quality, must clamp at 1.3, never below.
        foreach ([1.30, 1.32, 1.35, 1.40, 1.45, 1.50] as $ef) {
            foreach ([0, 1, 2] as $q) {
                $cases[] = ['category' => 'ease_factor_floor', 'prior_rep' => 2, 'prior_ef' => $ef, 'prior_interval' => 6, 'quality' => $q];
            }
        }

        // Lapse handling: q < 3 must reset repetition_num to 0 and interval to 1, regardless of prior state.
        foreach ([1, 2, 3, 4] as $rep) {
            foreach ([6, 15, 30] as $interval) {
                foreach ([0, 1, 2] as $q) {
                    $cases[] = ['category' => 'lapse_handling', 'prior_rep' => $rep, 'prior_ef' => 2.5, 'prior_interval' => $interval, 'quality' => $q];
                }
            }
        }

        return $cases;
    }

    /** Independently-derived expected next state — re-states the SM-2 spec, doesn't call the scheduler. */
    private function expectedStep(array $state, int $q): array
    {
        $rep = (int) $state['repetition_num'];
        $ef = (float) $state['ease_factor'];
        $interval = (int) $state['interval_days'];

        $newEf = $ef + (0.1 - (5 - $q) * (0.08 + (5 - $q) * 0.02));
        $newEf = max(1.30, round($newEf, 2));

        if ($q < 3) {
            return ['repetition_num' => 0, 'ease_factor' => $newEf, 'interval_days' => 1];
        }

        $newRep = $rep + 1;
        $newInterval = match (true) {
            $newRep <= 1 => 1,
            $newRep === 2 => 6,
            default => max(1, (int) round($interval * $newEf)),
        };

        return ['repetition_num' => $newRep, 'ease_factor' => $newEf, 'interval_days' => $newInterval];
    }
}
