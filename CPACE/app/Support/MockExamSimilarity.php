<?php

namespace App\Support;

use App\Models\MockExam;
use App\Models\MockExamAttempt;

/**
 * Answer-similarity screen for one exam.
 *
 * Two honest students who both know the material give the same RIGHT answers.
 * What is unusual is giving the same WRONG answer, because there are several
 * wrong choices to pick from. So for every pair of submitted students this
 * counts the questions both got wrong AND picked the same wrong choice, and
 * compares that with what pure chance would produce (each wrong choice equally
 * likely). Pairs far above chance are surfaced.
 *
 * It is a signal for a human to look at, never proof: students who studied from
 * the same wrong source will also match. It needs no camera and catches what
 * the proctoring cannot.
 */
class MockExamSimilarity
{
    /** Fewest identical wrong answers worth mentioning. */
    public const MIN_SHARED = 3;

    /** How many standard deviations above chance before a pair is listed. */
    public const MIN_Z = 2.0;

    /**
     * @return array<int, array<string, mixed>> strongest first
     */
    public function pairs(MockExam $exam, int $limit = 15): array
    {
        $items = $exam->items()->get();
        if ($items->isEmpty()) {
            return [];
        }

        // Per item: correct label and how many wrong choices there are to pick from.
        $key = [];
        foreach ($items as $item) {
            $choices = collect((array) $item->choices);
            $correct = $choices->first(fn ($c) => ! empty($c['is_correct']))['label'] ?? null;
            $wrongCount = $choices->count() - 1;
            if ($correct !== null && $wrongCount >= 2) {
                $key[(string) $item->id] = ['correct' => $correct, 'wrong_options' => $wrongCount];
            }
        }

        $attempts = MockExamAttempt::with('student:id,first_name,last_name')
            ->where('exam_id', $exam->id)
            ->where('status', MockExamAttempt::STATUS_SUBMITTED)
            ->get();

        // attempt id => [item id => the WRONG label they chose]
        $wrong = [];
        foreach ($attempts as $attempt) {
            $answers = (array) ($attempt->answers ?? []);
            foreach ($key as $itemId => $info) {
                $picked = $answers[$itemId] ?? null;
                if ($picked !== null && $picked !== $info['correct']) {
                    $wrong[$attempt->id][$itemId] = $picked;
                }
            }
        }

        $found = [];
        $list = $attempts->values();
        for ($i = 0; $i < $list->count(); $i++) {
            for ($j = $i + 1; $j < $list->count(); $j++) {
                $a = $wrong[$list[$i]->id] ?? [];
                $b = $wrong[$list[$j]->id] ?? [];
                $bothWrong = array_intersect_key($a, $b);
                if (count($bothWrong) < self::MIN_SHARED) {
                    continue;
                }

                $shared = 0;
                $mean = 0.0;
                $variance = 0.0;
                foreach ($bothWrong as $itemId => $labelA) {
                    $p = 1 / $key[$itemId]['wrong_options'];
                    $mean += $p;
                    $variance += $p * (1 - $p);
                    if ($labelA === $b[$itemId]) {
                        $shared++;
                    }
                }

                if ($shared < self::MIN_SHARED) {
                    continue;
                }

                $z = $variance > 0 ? ($shared - $mean) / sqrt($variance) : 0.0;
                if ($z < self::MIN_Z) {
                    continue;
                }

                $found[] = [
                    'a' => $list[$i],
                    'b' => $list[$j],
                    'shared' => $shared,
                    'both_wrong' => count($bothWrong),
                    'expected' => round($mean, 1),
                    'z' => round($z, 1),
                ];
            }
        }

        usort($found, fn ($x, $y) => [$y['z'], $y['shared']] <=> [$x['z'], $x['shared']]);

        return array_slice($found, 0, $limit);
    }
}
