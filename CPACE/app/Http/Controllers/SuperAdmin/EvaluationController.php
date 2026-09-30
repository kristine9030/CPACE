<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SpacedRepetitionScheduler;
use App\Services\WeaknessDetector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
    public function index()
    {
        return view('superadmin.evaluations', [
            'weakness' => $this->evaluateWeaknessDetection(),
            'sm2' => $this->verifySm2(),
        ]);
    }

    /**
     * Precision/Recall/F1 of WeaknessDetector::evaluate() against a simple
     * accuracy-threshold baseline (accuracy < 60%, ignoring the minimum-
     * attempts gate and the consecutive-wrong-streak rule).
     *
     * IMPORTANT — what this does and doesn't prove: no independently
     * labeled "this topic really is weak" dataset exists in production, so
     * this is NOT validation against ground truth. It measures how much the
     * module's two refinements (a 5-attempt floor before accuracy counts,
     * and an immediate 3-in-a-row-wrong trigger) change the call versus the
     * naive accuracy cutoff alone — i.e. it quantifies what the refinements
     * are doing, not whether the module is "right".
     */
    private function evaluateWeaknessDetection(): array
    {
        $detector = new WeaknessDetector();

        $records = DB::table('performance_records')
            ->select('student_id', 'topic_id', 'total_attempts', 'correct_count', 'consecutive_wrong')
            ->where('total_attempts', '>', 0)
            ->get();

        $tp = $fp = $fn = $tn = 0;

        foreach ($records as $record) {
            [$predictedWeak] = $detector->evaluate($record);
            $accuracy = $record->total_attempts > 0 ? $record->correct_count / $record->total_attempts : 0;
            $baselineWeak = $accuracy < WeaknessDetector::ACCURACY_THRESHOLD;

            match (true) {
                $predictedWeak && $baselineWeak => $tp++,
                $predictedWeak && ! $baselineWeak => $fp++,
                ! $predictedWeak && $baselineWeak => $fn++,
                default => $tn++,
            };
        }

        $precision = ($tp + $fp) > 0 ? round($tp / ($tp + $fp), 4) : null;
        $recall = ($tp + $fn) > 0 ? round($tp / ($tp + $fn), 4) : null;
        $f1 = ($precision !== null && $recall !== null && ($precision + $recall) > 0)
            ? round(2 * $precision * $recall / ($precision + $recall), 4)
            : null;

        return [
            'sample_size' => $records->count(),
            'tp' => $tp, 'fp' => $fp, 'fn' => $fn, 'tn' => $tn,
            'precision' => $precision, 'recall' => $recall, 'f1' => $f1,
        ];
    }

    /**
     * Runs the real SpacedRepetitionScheduler::next() through fixed quality
     * sequences and compares the result against expected values derived by
     * hand from the SM-2 spec (I(2)=6, I(n)=I(n-1)*EF,
     * EF'=EF+(0.1-(5-q)(0.08+(5-q)*0.02)), floored at 1.3) — independent of
     * the implementation, so a bug in the code can actually fail a case.
     */
    private function verifySm2(): array
    {
        $scheduler = new SpacedRepetitionScheduler();
        $anchor = Carbon::create(2026, 1, 1);

        $cases = [
            [
                'label' => 'Three easy-correct reviews in a row (q=5,5,5)',
                'qualities' => [5, 5, 5],
                'expected' => ['repetition_num' => 3, 'ease_factor' => 2.80, 'interval_days' => 17],
            ],
            [
                'label' => 'I(2)=6 spec check: two easy-correct reviews (q=5,5)',
                'qualities' => [5, 5],
                'expected' => ['repetition_num' => 2, 'ease_factor' => 2.70, 'interval_days' => 6],
            ],
            [
                'label' => 'Three moderate-correct reviews — q=4 leaves EF unchanged',
                'qualities' => [4, 4, 4],
                'expected' => ['repetition_num' => 3, 'ease_factor' => 2.50, 'interval_days' => 15],
            ],
            [
                'label' => 'A lapse after progress resets the chain (q=5,5,1)',
                'qualities' => [5, 5, 1],
                'expected' => ['repetition_num' => 0, 'ease_factor' => 2.16, 'interval_days' => 1],
            ],
            [
                'label' => 'Ease factor never drops below the 1.30 floor (q=0,0,0)',
                'qualities' => [0, 0, 0],
                'expected' => ['repetition_num' => 0, 'ease_factor' => 1.30, 'interval_days' => 1],
            ],
        ];

        $results = [];
        foreach ($cases as $case) {
            $state = ['repetition_num' => 0, 'ease_factor' => SpacedRepetitionScheduler::EF_DEFAULT, 'interval_days' => 0];
            foreach ($case['qualities'] as $q) {
                $state = $scheduler->next($state, $q, $anchor);
            }

            $actual = [
                'repetition_num' => $state['repetition_num'],
                'ease_factor' => $state['ease_factor'],
                'interval_days' => $state['interval_days'],
            ];

            $pass = $actual['repetition_num'] === $case['expected']['repetition_num']
                && abs($actual['ease_factor'] - $case['expected']['ease_factor']) < 0.005
                && $actual['interval_days'] === $case['expected']['interval_days'];

            $results[] = [
                'label' => $case['label'],
                'qualities' => $case['qualities'],
                'expected' => $case['expected'],
                'actual' => $actual,
                'pass' => $pass,
            ];
        }

        return [
            'cases' => $results,
            'passed' => collect($results)->where('pass', true)->count(),
            'total' => count($results),
        ];
    }
}
