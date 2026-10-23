<?php

namespace App\Support;

use App\Models\Question;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Scenarios (case problems) across the Test Bank, class quizzes, mock exams and
 * practice quizzes.
 *
 * The rules, in one place so every feature agrees on them:
 *  - a scenario is always taken whole: picking one of its questions brings in all
 *    of its (active, answerable) questions, kept together and in order;
 *  - quiz and exam items carry a COPY of the scenario text (scenario_group ties the
 *    items of one scenario together), never a live link, so editing the bank later
 *    cannot change something students have already sat;
 *  - scenario text is rich text and goes through RichText like any other.
 */
class ScenarioSupport
{
    private static ?bool $enabled = null;

    /** Whether this database has the scenario tables/columns (older or hand-built schemas do not). */
    public static function enabled(): bool
    {
        // Tests rebuild the schema per test (some without scenarios), so they must not reuse an earlier answer.
        if (self::$enabled !== null && ! app()->runningUnitTests()) {
            return self::$enabled;
        }

        return self::$enabled = Schema::hasTable('question_scenarios') && Schema::hasColumn('questions', 'scenario_id');
    }

    /** Forget the cached answer; for tests that create the schema after boot. */
    public static function forgetCache(): void
    {
        self::$enabled = null;
    }

    /** A question a student could actually answer: two or more choices and a marked correct one. */
    public static function isAnswerable(Question $q): bool
    {
        return $q->choices->count() >= 2 && $q->choices->contains('is_correct', true);
    }

    /**
     * Fields to copy onto a quiz/exam item from a bank question that belongs to a scenario.
     * Empty for a standalone question (and when the database has no scenario support).
     *
     * @return array<string, mixed>
     */
    public static function snapshotFields(Question $q): array
    {
        if (! self::enabled() || ! $q->scenario_id) {
            return [];
        }
        $scenario = $q->relationLoaded('scenario') ? $q->scenario : $q->scenario()->first();
        if (! $scenario) {
            return [];
        }

        return [
            'scenario_group' => 's' . $scenario->id,
            'scenario_text' => $scenario->body,
            'scenario_id' => $scenario->id,
        ];
    }

    /**
     * Make a picked list of bank questions scenario-complete: for every scenario
     * that appears, all of its active, answerable questions are included, kept
     * together at the place the first one appeared, in the scenario's own order.
     *
     * @param  Collection<int, Question>  $questions  questions with `choices` loaded
     * @return Collection<int, Question>
     */
    public static function expand(Collection $questions): Collection
    {
        if (! self::enabled() || $questions->isEmpty()) {
            return $questions;
        }

        $scenarioIds = $questions->pluck('scenario_id')->filter()->unique()->values();
        if ($scenarioIds->isEmpty()) {
            return $questions;
        }

        $byScenario = Question::with(['choices', 'scenario', 'topic.subject'])
            ->whereIn('scenario_id', $scenarioIds)
            ->where('is_active', true)
            ->orderBy('scenario_position')
            ->orderBy('id')
            ->get()
            ->filter(fn (Question $q) => self::isAnswerable($q))
            ->groupBy('scenario_id');

        $out = collect();
        $done = [];
        foreach ($questions as $q) {
            if (! $q->scenario_id) {
                $out->push($q);
                continue;
            }
            if (isset($done[$q->scenario_id])) {
                continue;
            }
            $done[$q->scenario_id] = true;
            $members = $byScenario->get($q->scenario_id);
            if ($members === null || $members->isEmpty()) {
                $out->push($q); // scenario has nothing answerable left; keep what was picked
                continue;
            }
            foreach ($members as $member) {
                $out->push($member);
            }
        }

        return $out->values();
    }

    /**
     * Clean the scenario fields of rows coming from a builder and make each scenario's
     * rows contiguous. Every row of one scenario shares one text; a scenario with no
     * text is refused.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>  the same rows, reordered, with scenario_group/scenario_text set (or null)
     */
    public static function normalise(array $rows, string $errorKey): array
    {
        $texts = [];
        foreach ($rows as $i => $row) {
            $group = self::cleanGroup($row['scenario_group'] ?? null);
            $rows[$i]['scenario_group'] = $group;
            if ($group === null) {
                $rows[$i]['scenario_text'] = null;
                continue;
            }
            if (! isset($texts[$group])) {
                $texts[$group] = (string) RichText::sanitize((string) ($row['scenario_text'] ?? ''));
            } elseif ($texts[$group] === '' && ! empty($row['scenario_text'])) {
                $texts[$group] = (string) RichText::sanitize((string) $row['scenario_text']);
            }
        }

        foreach ($texts as $group => $text) {
            if ($text === '') {
                throw ValidationException::withMessages([$errorKey => 'A scenario needs its text. Write the situation before adding questions to it.']);
            }
        }

        $ordered = [];
        $emitted = [];
        foreach ($rows as $row) {
            $group = $row['scenario_group'];
            if ($group === null) {
                $ordered[] = $row;
                continue;
            }
            if (isset($emitted[$group])) {
                continue;
            }
            $emitted[$group] = true;
            foreach ($rows as $member) {
                if ($member['scenario_group'] === $group) {
                    $ordered[] = $member + [];
                }
            }
        }

        return array_map(function (array $row) use ($texts) {
            $row['scenario_text'] = $row['scenario_group'] !== null ? $texts[$row['scenario_group']] : null;
            $row['scenario_id'] = $row['scenario_group'] !== null && ! empty($row['scenario_id']) ? (int) $row['scenario_id'] : null;

            return $row;
        }, $ordered);
    }

    /** "Question 3" or "Questions 3 to 5": which questions a scenario covers. */
    public static function label(int $firstNumber, int $size): string
    {
        return $size <= 1 ? 'Question ' . $firstNumber : 'Questions ' . $firstNumber . ' to ' . ($firstNumber + $size - 1);
    }

    /** Drop empty scenario_* keys so a row can be inserted into a table that has no scenario columns. */
    public static function stripEmptyScenario(array $row): array
    {
        foreach (['scenario_group', 'scenario_text', 'scenario_id'] as $key) {
            if (array_key_exists($key, $row) && ($row[$key] === null || $row[$key] === '')) {
                unset($row[$key]);
            }
        }

        return $row;
    }

    private static function cleanGroup(mixed $group): ?string
    {
        $group = is_scalar($group) ? preg_replace('/[^A-Za-z0-9_-]/', '', (string) $group) : '';

        return $group === '' ? null : substr($group, 0, 40);
    }

    /**
     * For views over quiz/exam items: where each scenario starts, so its text is shown once above
     * its first question. Keyed by item id.
     *
     * @param  iterable<object>  $items  items with scenario_group / scenario_text
     * @return array<int|string, array{start: bool, text: ?string, position: int, size: int, group: string}>
     */
    public static function mapItems(iterable $items): array
    {
        return self::map($items, fn ($i) => $i->scenario_group ?? null, fn ($i) => $i->scenario_text ?? null, fn ($i) => $i->id);
    }

    /**
     * Same, for bank Question models (practice quizzes): the group is the scenario id and the text
     * comes from the loaded `scenario` relation.
     *
     * @param  iterable<Question>  $questions
     */
    public static function mapQuestions(iterable $questions): array
    {
        return self::map(
            $questions,
            fn ($q) => $q->scenario_id ? 's' . $q->scenario_id : null,
            fn ($q) => $q->relationLoaded('scenario') ? $q->scenario?->body : null,
            fn ($q) => $q->id
        );
    }

    private static function map(iterable $items, callable $group, callable $text, callable $key): array
    {
        $list = collect($items)->values();
        $sizes = $list->map($group)->filter()->countBy()->all();
        $seen = [];
        $map = [];
        foreach ($list as $item) {
            $g = $group($item);
            if (! $g) {
                continue;
            }
            $seen[$g] = ($seen[$g] ?? 0) + 1;
            $map[$key($item)] = [
                'start' => $seen[$g] === 1,
                'text' => $text($item),
                'position' => $seen[$g],
                'size' => $sizes[$g],
                'group' => $g,
            ];
        }

        return $map;
    }

    /**
     * Reorder items so a scenario's questions stay together and shuffle whole units, not single
     * questions: used by "shuffle questions". The order inside a scenario is never shuffled.
     *
     * @template T of object
     * @param  Collection<int, T>  $items
     * @param  callable(object): int  $sortKey  per-unit sort key from the unit's first item
     * @return Collection<int, T>
     */
    public static function shuffleUnits(Collection $items, callable $sortKey): Collection
    {
        $units = [];
        foreach ($items as $item) {
            $g = $item->scenario_group ?? null;
            $k = $g ? 'g:' . $g : 'i:' . $item->id;
            $units[$k][] = $item;
        }

        return collect($units)
            ->sortBy(fn (array $unit) => $sortKey($unit[0]))
            ->flatten(1)
            ->values();
    }
}
