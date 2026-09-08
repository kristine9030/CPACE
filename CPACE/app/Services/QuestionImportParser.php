<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Turns an uploaded file into plain text (or, for images, leaves the file
 * for AiQuestionImportService's vision path) and, for text-based files,
 * tries a free rule-based pass first: split on numbered questions, detect
 * "A./A)" style choices and an "Answer:" line. This is fast, costs nothing,
 * and works well when a file follows a consistent pattern. Anything it can't
 * confidently parse is left for the AI fallback (see AiQuestionImportService).
 */
class QuestionImportParser
{
    public const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Extract plain text from a document file. Returns null for images
     * (handled separately via vision) or if extraction fails outright.
     */
    public function extractText(string $path, string $extension): ?string
    {
        try {
            return match (strtolower($extension)) {
                'pdf'          => $this->fromPdf($path),
                'docx', 'doc'  => $this->fromWord($path),
                'xlsx', 'xls'  => $this->fromSpreadsheet($path),
                'csv'          => $this->fromCsv($path),
                'txt'          => file_get_contents($path) ?: null,
                default        => null,
            };
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fromPdf(string $path): ?string
    {
        $text = (new PdfParser())->parseFile($path)->getText();

        return trim($text) !== '' ? $text : null;
    }

    private function fromWord(string $path): ?string
    {
        $document = WordIOFactory::load($path);
        $lines = [];

        foreach ($document->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $lines[] = $this->wordElementText($element);
            }
        }

        $text = implode("\n", array_filter($lines, fn ($l) => $l !== ''));

        return trim($text) !== '' ? $text : null;
    }

    private function wordElementText($element): string
    {
        if (method_exists($element, 'getText')) {
            return (string) $element->getText();
        }

        if (method_exists($element, 'getElements')) {
            $parts = array_map(fn ($e) => $this->wordElementText($e), $element->getElements());

            return implode(' ', array_filter($parts));
        }

        return '';
    }

    /**
     * Spreadsheets get two treatments: if the header row looks like a
     * question table (Question/Choice A.../Answer columns), the caller
     * should prefer parseSpreadsheetTable(); this text dump is the fallback
     * for free-form sheets.
     */
    private function fromSpreadsheet(string $path): ?string
    {
        $sheet = SpreadsheetIOFactory::load($path)->getActiveSheet();
        $lines = [];

        foreach ($sheet->toArray(null, true, true, false) as $row) {
            $cells = array_map(fn ($c) => trim((string) $c), $row);
            $line = implode(' | ', array_filter($cells, fn ($c) => $c !== ''));
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return $lines ? implode("\n", $lines) : null;
    }

    private function fromCsv(string $path): ?string
    {
        $lines = [];
        if (($handle = fopen($path, 'r')) === false) {
            return null;
        }

        while (($row = fgetcsv($handle)) !== false) {
            $cells = array_map(fn ($c) => trim((string) $c), $row);
            $line = implode(' | ', array_filter($cells, fn ($c) => $c !== ''));
            if ($line !== '') {
                $lines[] = $line;
            }
        }
        fclose($handle);

        return $lines ? implode("\n", $lines) : null;
    }

    /**
     * If a spreadsheet/CSV has a recognisable header (Question, Choice A-D or
     * Choices, Answer, Explanation), parse it row-by-row instead of relying
     * on the generic text pattern matcher — much higher confidence.
     *
     * @return array<int, array>|null
     */
    public function parseStructuredTable(string $path, string $extension): ?array
    {
        try {
            $rows = in_array(strtolower($extension), ['xlsx', 'xls'], true)
                ? SpreadsheetIOFactory::load($path)->getActiveSheet()->toArray(null, true, true, false)
                : $this->csvRows($path);
        } catch (\Throwable $e) {
            return null;
        }

        if (count($rows) < 2) {
            return null;
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $rows[0]);
        $col = fn (array $names) => $this->findColumn($header, $names);

        $qCol = $col(['question', 'question_text', 'question text']);
        $aCol = $col(['choice a', 'choice_a', 'option a', 'a']);
        $bCol = $col(['choice b', 'choice_b', 'option b', 'b']);
        $cCol = $col(['choice c', 'choice_c', 'option c', 'c']);
        $dCol = $col(['choice d', 'choice_d', 'option d', 'd']);
        $ansCol = $col(['answer', 'correct answer', 'correct_answer']);
        $expCol = $col(['explanation', 'rationale']);
        $typeCol = $col(['type', 'question type', 'question_type']);
        $diffCol = $col(['difficulty']);

        if ($qCol === null || $ansCol === null) {
            return null; // Not a recognisable question table.
        }

        $items = [];
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $questionText = trim((string) ($row[$qCol] ?? ''));
            if ($questionText === '') {
                continue;
            }

            $answerRaw = trim((string) ($row[$ansCol] ?? ''));
            $isTrueFalse = $typeCol !== null && stripos((string) ($row[$typeCol] ?? ''), 'true') !== false
                || ($aCol === null && preg_match('/^(true|false|t|f)$/i', $answerRaw));

            if ($isTrueFalse || $aCol === null) {
                $isTrue = preg_match('/^(true|t)$/i', $answerRaw) === 1;
                $items[] = [
                    'question_text'  => $questionText,
                    'question_type'  => 'true_false',
                    'choices'        => [
                        ['label' => 'A', 'text' => 'True', 'is_correct' => $isTrue],
                        ['label' => 'B', 'text' => 'False', 'is_correct' => ! $isTrue],
                    ],
                    'explanation'    => $expCol !== null ? trim((string) ($row[$expCol] ?? '')) : null,
                    'difficulty'     => $this->normalizeDifficulty($diffCol !== null ? (string) ($row[$diffCol] ?? '') : ''),
                    'confidence'     => 90,
                ];
                continue;
            }

            $choiceCols = ['A' => $aCol, 'B' => $bCol, 'C' => $cCol, 'D' => $dCol];
            $choices = [];
            $correctLetter = strtoupper(preg_replace('/[^A-Da-d]/', '', $answerRaw));
            $correctLetter = $correctLetter !== '' ? $correctLetter[0] : null;

            foreach ($choiceCols as $label => $colIndex) {
                if ($colIndex === null) {
                    continue;
                }
                $text = trim((string) ($row[$colIndex] ?? ''));
                if ($text === '') {
                    continue;
                }
                $choices[] = [
                    'label'      => $label,
                    'text'       => $text,
                    'is_correct' => $label === $correctLetter,
                ];
            }

            if (count($choices) < 2) {
                continue;
            }

            $hasCorrect = collect($choices)->contains('is_correct', true);

            $items[] = [
                'question_text' => $questionText,
                'question_type' => 'mcq',
                'choices'       => $choices,
                'explanation'   => $expCol !== null ? trim((string) ($row[$expCol] ?? '')) : null,
                'difficulty'    => $this->normalizeDifficulty($diffCol !== null ? (string) ($row[$diffCol] ?? '') : ''),
                'confidence'    => $hasCorrect ? 90 : 40,
            ];
        }

        return $items ?: null;
    }

    private function csvRows(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) === false) {
            return [];
        }
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $this->stripBom($rows);
    }

    /**
     * Excel/faculty-saved CSVs are commonly UTF-8-with-BOM; fgetcsv() leaves
     * those 3 bytes glued to the first header cell (e.g. "\xEF\xBB\xBFQuestion"),
     * which silently breaks the header-name matching in findColumn(). Strip it
     * from the very first cell only.
     */
    private function stripBom(array $rows): array
    {
        if (isset($rows[0][0])) {
            $rows[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $rows[0][0]);
        }

        return $rows;
    }

    private function findColumn(array $header, array $names): ?int
    {
        foreach ($header as $index => $value) {
            if (in_array($value, $names, true)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Rule-based pass over free-form text: split into blocks starting with
     * "1.", "1)", "Q1." etc, then look for "A./A)" choice lines and an
     * "Answer:" line within the block. Returns null (not an empty array)
     * when nothing that looks like a question was found, so the caller
     * knows to fall back to AI rather than "successfully" importing zero
     * questions.
     *
     * @return array<int, array>|null
     */
    public function parseFreeText(string $text): ?array
    {
        $text = str_replace("\r\n", "\n", $text);
        // Split right before a line that starts a new numbered question.
        $blocks = preg_split('/\n(?=\s*(?:Q\.?\s*)?\d{1,3}[\.\)]\s+\S)/', "\n" . $text);

        $items = [];
        foreach ($blocks as $block) {
            $item = $this->parseBlock($block);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items ?: null;
    }

    private function parseBlock(string $block): ?array
    {
        $lines = array_values(array_filter(array_map('trim', explode("\n", $block)), fn ($l) => $l !== ''));
        if (empty($lines)) {
            return null;
        }

        // First line must look like a numbered question stem.
        if (! preg_match('/^(?:Q\.?\s*)?\d{1,3}[\.\)]\s*(.+)$/i', $lines[0], $m)) {
            return null;
        }
        $questionText = trim($m[1]);

        $choices = [];
        $answerLetter = null;
        $tfAnswer = null;
        $explanation = null;

        foreach (array_slice($lines, 1) as $line) {
            if (preg_match('/^([A-D])[\.\)]\s*(.+)$/i', $line, $m)) {
                $choices[strtoupper($m[1])] = trim($m[2]);
                continue;
            }
            if (preg_match('/^(?:answer|ans|correct answer)\s*[:\-]\s*(.+)$/i', $line, $m)) {
                $raw = trim($m[1]);
                if (preg_match('/^(true|false)$/i', $raw)) {
                    $tfAnswer = strtolower($raw) === 'true';
                } else {
                    $letter = strtoupper(preg_replace('/[^A-Da-d]/', '', $raw));
                    $answerLetter = $letter !== '' ? $letter[0] : null;
                }
                continue;
            }
            if (preg_match('/^(?:explanation|rationale)\s*[:\-]\s*(.+)$/i', $line, $m)) {
                $explanation = trim($m[1]);
                continue;
            }
            // A continuation line (question stem or choice wrapped to a new
            // line) — append to whichever we last saw.
            if (! empty($choices)) {
                $lastLabel = array_key_last($choices);
                $choices[$lastLabel] .= ' ' . $line;
            } else {
                $questionText .= ' ' . $line;
            }
        }

        if (empty($choices) && $tfAnswer === null) {
            // Could still be a True/False stem with no explicit choice lines,
            // but with no answer marker at all there's nothing to grade against.
            return null;
        }

        if (empty($choices)) {
            return [
                'question_text' => $questionText,
                'question_type' => 'true_false',
                'choices'       => [
                    ['label' => 'A', 'text' => 'True', 'is_correct' => $tfAnswer === true],
                    ['label' => 'B', 'text' => 'False', 'is_correct' => $tfAnswer === false],
                ],
                'explanation'   => $explanation,
                'difficulty'    => 'moderate',
                'confidence'    => 80,
            ];
        }

        if (count($choices) < 2) {
            return null;
        }

        $builtChoices = [];
        foreach ($choices as $label => $text) {
            $builtChoices[] = ['label' => $label, 'text' => $text, 'is_correct' => $label === $answerLetter];
        }
        $hasCorrect = $answerLetter !== null && isset($choices[$answerLetter]);

        return [
            'question_text' => $questionText,
            'question_type' => 'mcq',
            'choices'       => $builtChoices,
            'explanation'   => $explanation,
            'difficulty'    => 'moderate',
            'confidence'    => $hasCorrect ? 85 : 35,
        ];
    }

    private function normalizeDifficulty(string $raw): string
    {
        $raw = strtolower(trim($raw));

        return match (true) {
            str_starts_with($raw, 'easy'), $raw === 'e' => 'easy',
            str_starts_with($raw, 'hard'), str_starts_with($raw, 'difficult'), $raw === 'h' => 'difficult',
            default => 'moderate',
        };
    }
}
