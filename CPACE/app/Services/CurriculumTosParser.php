<?php

namespace App\Services;

use Smalot\PdfParser\Parser as PdfParser;

/**
 * Reads the PRC Board of Accountancy "Table of Specifications" (TOS) PDF —
 * the official LECPA syllabus layout — into a topic outline per subject.
 *
 * The PDF is a table, but its extracted text arrives in reading order: the
 * "Topics and Outcomes" column first, then the Weight / No. of Items numbers
 * as a separate run of lines. So the outline is rebuilt from the numbering
 * itself ("A." / "1." / "1.1" / "1.0"), wrapped lines are joined back onto
 * their heading, and each weight ("5.71% 4") is attached to the heading it
 * belongs to: the heading on the same line, otherwise the shallowest heading
 * since the previous weight.
 *
 * This targets the official PRC layout only. Anything it isn't sure about is
 * surfaced for the chair to review; nothing is saved from here.
 */
class CurriculumTosParser
{
    /** Subject detection: header text => CPACE subject code. Order matters (AFAR before FAR). */
    public const SUBJECT_HEADERS = [
        'advanced financial accounting' => 'AFAR',
        'financial accounting and reporting' => 'FAR',
        'management services' => 'MS',
        'management advisory' => 'MS',
        'regulatory framework' => 'RFBT',
        'auditing' => 'AUD',
        'taxation' => 'TAX',
    ];

    /** Topic names longer than the topics.name column are cut here (full text kept separately). */
    public const MAX_NAME = 150;

    private const NOISE = [
        '/^table of specifications/i',
        '/^in\s+[A-Z ,&]+$/',
        '/^effective october/i',
        '/^philippine qu?[ai]l?[ai]?f/i',
        '/^difficulty level/i',
        '/^bloom/i',
        '/^(remembering|understanding|applying|application|analyzing|evaluating|creating)\b/i',
        '/^topics and outcomes?/i',
        '/^weight\b/i',
        '/^(in|percent|items|no\.?)$/i',
        '/^no\. of/i',
        '/^\(in$/i',
        '/^percent\)/i',
        '/^the examinees? can perform/i',
        '/^topic:?$/i',
        '/^easy \(30%\)/i',
        '/^moderate/i',
        '/^difficult \(30%\)/i',
        '/^(taxation|auditing)$/i',
    ];

    private const CONNECTORS = ['of', 'and', 'the', 'for', 'to', 'in', 'with', 'a', 'an', 'or', 'from', 'on', 'by', 'at', 'its', 'their', 'as', 'under'];

    /**
     * @return array<string, list<array{ref: string, name: string, full_name: string, depth: int, parent: ?int, weight: ?float, items: ?int}>>
     *         subject code => ordered outline (parent = index into the same list)
     */
    public function parseFile(string $path): array
    {
        $text = (new PdfParser())->parseFile($path)->getText();

        return $this->parseText($text);
    }

    /** @return array<string, list<array{ref: string, name: string, full_name: string, depth: int, parent: ?int, weight: ?float, items: ?int}>> */
    public function parseText(string $text): array
    {
        $sections = $this->splitBySubject($text);

        return array_map(fn (array $lines) => $this->outline($lines), $sections);
    }

    /**
     * Split the whole document into one line list per subject, keyed by the
     * CPACE subject code its "Table of Specifications in ..." header names.
     *
     * @return array<string, list<string>>
     */
    private function splitBySubject(string $text): array
    {
        $lines = preg_split('/\R/u', str_replace(["\t", "\u{00A0}"], ' ', $text));
        $sections = [];
        $current = null;
        $awaitingName = false;

        foreach ($lines as $raw) {
            $line = trim(preg_replace('/\s+/u', ' ', $raw));
            if ($line === '') {
                $current !== null && ($sections[$current][] = '');
                continue;
            }

            if (preg_match('/^table of specifications\b(.*)$/i', $line, $m)) {
                $awaitingName = true;
                $code = $this->subjectCode($m[1]);
                if ($code) {
                    $current = $code;
                    $awaitingName = false;
                }
                continue;
            }

            if ($awaitingName) {
                $code = $this->subjectCode($line);
                if ($code) {
                    $current = $code;
                    $awaitingName = false;
                    $sections[$current] ??= [];
                    continue;
                }
            }

            if ($current !== null) {
                $sections[$current][] = $line;
            }
        }

        return array_filter($sections);
    }

    private function subjectCode(string $header): ?string
    {
        $header = mb_strtolower($header);
        foreach (self::SUBJECT_HEADERS as $needle => $code) {
            if (str_contains($header, $needle)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $lines
     * @return list<array{ref: string, name: string, full_name: string, depth: int, parent: ?int, weight: ?float, items: ?int}>
     */
    private function outline(array $lines): array
    {
        $items = [];
        $open = null;              // index of the heading still collecting wrapped lines
        $prevLine = '';
        $topIndex = null;          // current top-level (letter / "N.0") heading
        $topNumber = null;         // "N" when the top level is the AFAR-style "N.0"
        $byNumber = [];            // "1.2" => index, scoped to the current top-level heading
        $sinceWeight = [];         // indexes seen since the last weight was assigned
        $awaitingCount = null;     // heading that got a weight but whose item count is on the next line

        foreach ($lines as $line) {
            if ($line === '' || $this->isNoise($line)) {
                $open = null;
                $prevLine = '';
                continue;
            }
            if (preg_match('/^total\b/i', $line)) {
                $open = null;
                continue;
            }

            [$body, $weight, $count] = $this->splitWeight($line);

            // A line that is only numbers (the Weight / No. of Items column).
            if ($body === '' || preg_match('/^[\d\s.%]+$/', $body)) {
                if ($weight !== null) {
                    $target = $this->weightTarget($items, $sinceWeight);
                    if ($target !== null) {
                        $items[$target]['weight'] = $weight;
                        $items[$target]['items'] = $count;
                        $sinceWeight = [];
                        $awaitingCount = $count === null ? $target : null;
                    }
                } elseif ($awaitingCount !== null && preg_match('/^(\d{1,3})\b/', $body, $n)) {
                    $items[$awaitingCount]['items'] = (int) $n[1];
                    $awaitingCount = null;
                }
                $open = null;
                $prevLine = '';
                continue;
            }
            $awaitingCount = null;

            if ($heading = $this->heading($body)) {
                [$ref, $name, $isTop] = $heading;

                // "No. of Items" columns that landed on the heading's own line
                // without a weight ("Property, plant and equipment 4").
                if (preg_match('/^(.*?\D)((?:\s+\d{1,3})+)$/u', $name, $tail)) {
                    $name = trim($tail[1]);
                    $count ??= (int) preg_split('/\s+/', trim($tail[2]))[0];
                }

                if ($isTop) {
                    $parent = null;
                    $byNumber = [];
                    $topNumber = preg_match('/^(\d+)\.0$/', $ref, $n) ? $n[1] : null;
                } else {
                    // In the "N.0" layout a bare "1." under "3.0" is really "3.1".
                    if ($topNumber !== null && ! str_contains($ref, '.')) {
                        $ref = $topNumber . '.' . $ref;
                    }
                    $parent = $this->parentFor($ref, $byNumber) ?? $topIndex;
                }

                $items[] = [
                    'ref' => $ref,
                    'name' => $name,
                    'full_name' => $name,
                    'depth' => $parent === null ? 0 : $items[$parent]['depth'] + 1,
                    'parent' => $parent,
                    'weight' => null,
                    'items' => null,
                ];
                $index = array_key_last($items);

                if ($isTop) {
                    $topIndex = $index;
                } else {
                    $byNumber[$ref] = $index;
                }

                if ($weight !== null) {
                    $items[$index]['weight'] = $weight;
                    $items[$index]['items'] = $count;
                    $sinceWeight = [];
                    $awaitingCount = $count === null ? $index : null;
                } else {
                    $items[$index]['items'] = $count;
                    $sinceWeight[] = $index;
                }

                $open = $index;
                $prevLine = $body;
                continue;
            }

            // Wrapped continuation of the heading above.
            if ($open !== null && $this->continues($prevLine, $body)) {
                $items[$open]['full_name'] = trim($items[$open]['full_name'] . ' ' . $body);
                if ($weight !== null) {
                    $items[$open]['weight'] = $weight;
                    $items[$open]['items'] = $count;
                    $sinceWeight = [];
                }
                $prevLine = $body;
                continue;
            }

            // Unnumbered prose (an outcome sentence) — not a topic.
            $open = null;
            $prevLine = $body;
        }

        foreach ($items as &$item) {
            $item['full_name'] = $this->clean($item['full_name']);
            $item['name'] = mb_strlen($item['full_name']) > self::MAX_NAME
                ? rtrim(mb_substr($item['full_name'], 0, self::MAX_NAME - 1)) . '…'
                : $item['full_name'];
        }

        return $items;
    }

    /**
     * Recognise a numbered heading. Top level is a capital letter ("A.",
     * "B.") or a whole number with ".0" ("1.0" — the AFAR layout).
     *
     * @return array{0: string, 1: string, 2: bool}|null  [ref, text, isTopLevel]
     */
    private function heading(string $line): ?array
    {
        if (preg_match('/^([A-Z])\.\s+(\S.*)$/u', $line, $m)) {
            return [$m[1] . '.', $m[2], true];
        }
        if (preg_match('/^(\d+\.0)\s+([A-Za-z(].*)$/u', $line, $m)) {
            return [$m[1], $m[2], true];
        }
        if (preg_match('/^(\d{1,2}(?:\.\d{1,2})*)\.?\s*\.?\s+([A-Za-z(“"\'].*)$/u', $line, $m)) {
            return [$m[1], $m[2], false];
        }

        return null;
    }

    /** Nearest earlier heading whose number is a prefix: "1.2.1" -> "1.2" -> "1". */
    private function parentFor(string $ref, array $byNumber): ?int
    {
        $parts = explode('.', $ref);
        while (count($parts) > 1) {
            array_pop($parts);
            $prefix = implode('.', $parts);
            if (isset($byNumber[$prefix])) {
                return $byNumber[$prefix];
            }
        }

        return null;
    }

    /** Shallowest heading since the last weight that still has none. */
    private function weightTarget(array $items, array $sinceWeight): ?int
    {
        $best = null;
        foreach ($sinceWeight as $index) {
            if ($items[$index]['weight'] !== null) {
                continue;
            }
            if ($best === null || $items[$index]['depth'] < $items[$best]['depth']) {
                $best = $index;
            }
        }

        return $best;
    }

    /**
     * Pull a trailing "5.71% 4 ..." off a line.
     *
     * @return array{0: string, 1: ?float, 2: ?int}
     */
    private function splitWeight(string $line): array
    {
        if (preg_match('/^(.*?)\s*(\d{1,3}(?:\.\d{1,2})?)\s*%\s*(\d{1,3})?/u', $line, $m)) {
            return [trim($m[1]), (float) $m[2], isset($m[3]) && $m[3] !== '' ? (int) $m[3] : null];
        }

        return [$line, null, null];
    }

    /** Is $line the wrapped rest of $prev? The PDF column wraps at roughly 55-70 characters. */
    private function continues(string $prev, string $line): bool
    {
        if ($prev === '') {
            return false;
        }
        if (preg_match('/^\p{Ll}/u', $line)) {
            return true;
        }
        if (preg_match('/[,\-(&]$/u', $prev)) {
            return true;
        }
        $last = mb_strtolower((string) preg_replace('/^.*\s/u', '', rtrim($prev)));
        if (in_array($last, self::CONNECTORS, true)) {
            return true;
        }

        return mb_strlen($prev) >= 55 && ! preg_match('/[.:;]$/u', $prev);
    }

    private function isNoise(string $line): bool
    {
        if (preg_match('/^\d{1,2}$/', $line)) {
            return false; // bare numbers belong to the item-count column, handled by the caller
        }
        foreach (self::NOISE as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    private function clean(string $text): string
    {
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = preg_replace('/\s+([,.;:)])/u', '$1', $text);

        return trim($text, " \t.");
    }
}
