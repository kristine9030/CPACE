<?php

namespace App\Services;

use App\Models\Question;

/**
 * Decides what wording a student sees for a question on a given attempt, so the
 * exact phrasing varies between sittings and rote memorisation is discouraged -
 * while the meaning, and therefore the correct answer, stays identical. The
 * faculty's stored question is NEVER changed; this only produces an in-memory
 * copy at render time.
 *
 * The phrasing SOURCE is pluggable (this is the future-proof part):
 *   1. Human-written variants stored in `question_variants` (source = faculty)
 *   2. AI-generated variants stored in the same table (source = ai)
 *   3. The built-in rule-based paraphraser below (no storage, always available)
 *
 * forDisplay() picks among the original text + any stored variants + a
 * rule-based rewrite, deterministically by a per-session seed:
 *   - the same attempt always shows the same wording (quiz page == results page)
 *   - a retake (a new session) produces a different wording
 *
 * Every rule-based transformation is meaning-preserving: technical terms are
 * left intact and only neutral framing words and safe synonyms are varied.
 */
class QuestionParaphraser
{
    /**
     * Resolve the wording to display for a question on this attempt.
     *
     * Builds a pool of candidate phrasings (the original, any stored
     * faculty/AI variants, and a rule-based rewrite) and picks one
     * deterministically from the seed. Stored variants need the `variants`
     * relation to be eager-loaded; if it isn't, only the rule-based path runs.
     */
    public static function forDisplay(Question $question, int $seed, string $type = 'mcq'): string
    {
        $original = trim($question->question_text);

        // Human/AI-written variants for this question, if the relation is loaded.
        $stored = $question->relationLoaded('variants')
            ? $question->variants->where('is_active', true)->pluck('variant_text')->all()
            : [];

        if (! empty($stored)) {
            // Curated phrasings exist - rotate among the original and those.
            $pool = array_values(array_filter(array_merge([$original], $stored)));

            return $pool[self::pick($seed, 'variant', count($pool))];
        }

        // No curated variants - fall back to the rule-based rewrite.
        return self::rephrase($original, $seed, $type);
    }

    /**
     * Synonym swaps that never change meaning. Longer phrases are listed before
     * the shorter words they contain. Each swap is toggled independently by the
     * seed, so different attempts get different combinations.
     */
    private const SYNONYMS = [
        // multi-word phrases first (matched before the words they contain)
        'is best described as'   => 'is best characterized as',
        'which of the following' => 'which of these',
        'all of the following'   => 'each of the following',
        'none of the following'  => 'not one of the following',
        'the entry to'           => 'the journal entry to',
        'is computed as'         => 'is calculated as',
        'arises when'            => 'occurs when',
        'results when'           => 'happens when',
        'in lieu of'             => 'instead of',
        'in accordance with'     => 'consistent with',
        'with respect to'        => 'regarding',
        'as part of'             => 'as a component of',
        'in a period of'         => 'during a period of',
        'for purposes of'        => 'for the purpose of',
        'is required to'         => 'must',
        'refers to'              => 'pertains to',
        'is treated'             => 'is handled',
        'is recognized'          => 'is recorded',
        'is measured at'         => 'is carried at',
        'is presented'           => 'is shown',
        'is classified as'       => 'is categorized as',
        'gives rise to'          => 'creates',
        // single words
        'computed'      => 'calculated',
        'determine'     => 'identify',
        'yields'        => 'produces',
        'generally'     => 'typically',
        'primarily'     => 'mainly',
        'usually'       => 'ordinarily',
        'approximately' => 'roughly',
        'amount'        => 'sum',
        'entity'        => 'company',
        'firm'          => 'business',
        'permitted'     => 'allowed',
        'prohibited'    => 'not allowed',
        'appropriate'   => 'proper',
        'incurred'      => 'sustained',
        'subsequent'    => 'later',
    ];

    /**
     * Alternative lead-ins for the common CPALE question stems. The first
     * pattern that matches wins. $1 back-references preserve any negation
     * ("NOT") and verb exactly so the question's logic is untouched.
     */
    private const FRAMES = [
        // "Which of the following is NOT ..." - negation preserved verbatim.
        '/^Which of the following (is|are|was|were) NOT\b/i' => [
            'Which of the following $1 NOT',
            'Which of these $1 NOT',
            'Identify which of the following $1 NOT',
            'Among the options below, which $1 NOT',
            'Of the following, which $1 NOT',
        ],
        // "Which of the following ..." (no negation)
        '/^Which of the following\b/i' => [
            'Which of the following',
            'Which of these',
            'Among the following, which',
            'Identify which of the following',
            'Of the choices below, which',
        ],
        // Bare "Which ..." e.g. "Which cost is NOT capitalized ..."
        '/^Which\b/i' => [
            'Which',
            'Identify which',
            'Determine which',
        ],
        // "What is/are ..."
        '/^What (is|are)\b/i' => [
            'What $1',
            'Identify what $1',
            'Determine what $1',
        ],
        // "Under the TRAIN law ...", "Under the allowance method ..."
        '/^Under (the|a|an)\b/i' => [
            'Under $1',
            'Based on $1',
            'According to $1',
            'In line with $1',
        ],
        // "How is X treated/computed/recognized?"
        '/^How (is|are|does|do)\b/i' => [
            'How $1',
            'In what way $1',
        ],
        // "An entity ...", "A company ..."
        '/^An entity\b/i' => [
            'An entity',
            'A reporting entity',
            'A company',
        ],
    ];

    /**
     * Vocabulary exposed to the faculty UI so it can suggest helpful words while
     * a variant is being typed: alternative question openers and safe synonym
     * swaps. The UI uses these only as guidance - the faculty can ignore them.
     */
    public static function vocabulary(): array
    {
        $synonyms = [];
        foreach (self::SYNONYMS as $from => $to) {
            $synonyms[] = ['from' => $from, 'to' => $to];
        }

        return [
            'openers' => [
                'Which of the following', 'Which of these', 'Identify which',
                'Determine which', 'Among the following, which', 'Of the choices below, which',
                'What is', 'Based on the', 'According to the',
            ],
            'synonyms' => $synonyms,
        ];
    }

    /**
     * Produce a reworded copy of $text for the given seed (rule-based path).
     */
    public static function rephrase(string $text, int $seed, string $type = 'mcq'): string
    {
        $text = trim($text);

        $text = self::applyFrame($text, $seed);
        $text = self::applySynonyms($text, $seed);
        $text = self::applyPrefix($text, $seed, $type);

        return $text;
    }

    /**
     * Vary the lead-in frame (first matching pattern wins).
     */
    private static function applyFrame(string $text, int $seed): string
    {
        foreach (self::FRAMES as $pattern => $options) {
            if (preg_match($pattern, $text)) {
                $choice = $options[self::pick($seed, 'frame', count($options))];

                return preg_replace($pattern, $choice, $text, 1);
            }
        }

        return $text;
    }

    /**
     * Apply a seed-selected subset of the safe synonym swaps.
     */
    private static function applySynonyms(string $text, int $seed): string
    {
        $i = 0;
        foreach (self::SYNONYMS as $from => $to) {
            $i++;
            // Each swap is on/off depending on the seed -> compounding variety.
            if (self::pick($seed, 'syn' . $i, 2) === 0) {
                continue;
            }
            $text = preg_replace('/\b' . preg_quote($from, '/') . '\b/i', $to, $text, 1);
        }

        return $text;
    }

    /**
     * Optionally add a neutral instructional prefix for fill-in-the-blank style
     * stems (ending in ":") and True/False statements. Never alters the body.
     */
    private static function applyPrefix(string $text, int $seed, string $type): string
    {
        $isStatement = str_ends_with($text, ':');
        $startsInterrogative = (bool) preg_match('/^(Which|What|How|When|Why|Where|Identify|Among|Of|Determine)\b/i', $text);

        if ($type === 'true_false') {
            $options = ['', 'True or False: ', 'Evaluate this statement: ', 'State whether this is true or false: '];
        } elseif ($isStatement && ! $startsInterrogative) {
            $options = ['', 'Complete the statement: ', 'Choose the option that best completes: ', 'Fill in the blank: '];
        } else {
            return $text;
        }

        $prefix = $options[self::pick($seed, 'pre', count($options))];

        return $prefix === '' ? $text : $prefix . $text;
    }

    /**
     * Deterministic index in [0, $n) derived from the seed and a salt.
     */
    private static function pick(int $seed, string $salt, int $n): int
    {
        if ($n <= 0) {
            return 0;
        }

        return (crc32($seed . '|' . $salt) % $n + $n) % $n;
    }
}
