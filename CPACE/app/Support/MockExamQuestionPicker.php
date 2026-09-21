<?php

namespace App\Support;

use App\Models\Question;
use Illuminate\Support\Collection;

/**
 * Picks the questions for a mock exam when faculty uses the "auto" toggle.
 *
 * A real CPALE subject paper is balanced two ways at once: it spreads across
 * the syllabus rather than dwelling on one topic, and it mixes difficulty
 * rather than being uniformly easy. So the picker allocates the requested
 * item count evenly across the selected topics, then aims for roughly
 * 30% easy / 50% moderate / 20% difficult within each topic.
 *
 * Test banks are uneven in practice - one topic may have 200 questions and
 * another 6 - so every quota degrades gracefully: a difficulty short on stock
 * borrows from the other difficulties in the same topic, and a topic that
 * cannot fill its share hands the remainder back to the topics that can.
 * The picker therefore returns "as many as it could", and the caller decides
 * whether a shortfall is acceptable.
 */
class MockExamQuestionPicker
{
    /** Target difficulty mix. Keys match questions.difficulty in the schema. */
    private const MIX = [
        'easy' => 0.30,
        'moderate' => 0.50,
        'difficult' => 0.20,
    ];

    /**
     * @param  array<int>  $topicIds
     * @return Collection<int, Question>  questions with `choices` eager loaded
     */
    public static function pick(array $topicIds, int $count): Collection
    {
        $topicIds = array_values(array_unique(array_filter($topicIds)));
        if ($topicIds === [] || $count < 1) {
            return collect();
        }

        $pool = self::pool($topicIds);
        $quotas = self::spreadEvenly($count, $topicIds);

        $picked = collect();
        $shortfall = 0;

        foreach ($quotas as $topicId => $quota) {
            $taken = self::takeFromTopic($pool, $topicId, $quota);
            $picked = $picked->concat($taken);
            $shortfall += $quota - $taken->count();
        }

        // Topics that came up short release their share to whatever stock is
        // left anywhere else in the selection. Depth 2, because the pool is
        // nested topic => difficulty => questions.
        if ($shortfall > 0) {
            $remaining = $pool->flatten(2)->shuffle()->take($shortfall);
            $picked = $picked->concat($remaining);
        }

        return $picked->shuffle()->values();
    }

    /**
     * Active questions for the selected topics, grouped topic => difficulty
     * => shuffled questions. Shuffled once here so every later `take()` is a
     * random sample rather than always the lowest ids.
     *
     * @param  array<int>  $topicIds
     */
    private static function pool(array $topicIds): Collection
    {
        return Question::with('choices')
            ->whereIn('topic_id', $topicIds)
            ->where('is_active', true)
            ->get()
            // A question with no correct choice would be unanswerable, and one
            // with fewer than two choices is not a question - skip both rather
            // than letting them reach an exam paper.
            ->filter(fn (Question $q) => $q->choices->count() >= 2 && $q->choices->contains('is_correct', true))
            ->shuffle()
            ->groupBy('topic_id')
            ->map(fn (Collection $forTopic) => $forTopic->groupBy('difficulty'));
    }

    /**
     * Split `count` as evenly as possible across the topics, giving the
     * remainder to the earliest ones (so 70 across 4 topics is 18/18/17/17).
     *
     * @param  array<int>  $topicIds
     * @return array<int, int>  topic id => quota
     */
    private static function spreadEvenly(int $count, array $topicIds): array
    {
        $topicCount = count($topicIds);
        $base = intdiv($count, $topicCount);
        $remainder = $count % $topicCount;

        $quotas = [];
        foreach (array_values($topicIds) as $i => $topicId) {
            $quotas[$topicId] = $base + ($i < $remainder ? 1 : 0);
        }

        return $quotas;
    }

    /**
     * Fill one topic's quota, honouring the difficulty mix where stock allows
     * and falling back to whatever that topic still has when it does not.
     *
     * Questions are removed from the pool as they're taken so the later
     * shortfall sweep can never hand back a duplicate.
     */
    private static function takeFromTopic(Collection $pool, int $topicId, int $quota): Collection
    {
        if ($quota < 1 || ! $pool->has($topicId)) {
            return collect();
        }

        $taken = collect();

        foreach (self::difficultyQuotas($quota) as $difficulty => $want) {
            $taken = $taken->concat(self::drain($pool, $topicId, $difficulty, $want));
        }

        // Mix quotas went unmet somewhere - top up from this topic's remaining
        // stock at any difficulty before giving up on it.
        $still = $quota - $taken->count();
        foreach (array_keys(self::MIX) as $difficulty) {
            if ($still < 1) {
                break;
            }
            $extra = self::drain($pool, $topicId, $difficulty, $still);
            $taken = $taken->concat($extra);
            $still -= $extra->count();
        }

        return $taken;
    }

    /**
     * Turn a topic quota into per-difficulty targets. Easy and moderate are
     * rounded and difficult takes the remainder, so the parts always sum back
     * to the quota exactly.
     *
     * @return array<string, int>
     */
    private static function difficultyQuotas(int $quota): array
    {
        $easy = (int) round($quota * self::MIX['easy']);
        $moderate = (int) round($quota * self::MIX['moderate']);
        $difficult = max(0, $quota - $easy - $moderate);

        return ['easy' => $easy, 'moderate' => $moderate, 'difficult' => $difficult];
    }

    /** Take up to `$want` questions of one difficulty, removing them from the pool. */
    private static function drain(Collection $pool, int $topicId, string $difficulty, int $want): Collection
    {
        if ($want < 1) {
            return collect();
        }

        $byDifficulty = $pool->get($topicId);
        $available = $byDifficulty?->get($difficulty);
        if (! $available || $available->isEmpty()) {
            return collect();
        }

        $taken = $available->take($want);
        $byDifficulty->put($difficulty, $available->slice($taken->count())->values());

        return $taken->values();
    }
}
