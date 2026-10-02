<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * A question's "exhibit": an optional picture and/or a table shown under the
 * question text (journal entries, trial balances, schedules, charts).
 *
 * The picture is a file on the public disk; the table is a small JSON document
 * — {"header": bool, "total": bool, "rows": [[cell, ...], ...]} — that is
 * validated here and always printed with escaped cells, never as raw HTML.
 */
class QuestionExhibit
{
    public const DIRECTORY = 'question-images';
    public const MAX_COLUMNS = 12;
    public const MAX_ROWS = 40;
    public const MAX_CELL_LENGTH = 150;
    public const MAX_IMAGE_KILOBYTES = 3072;

    public static function storeImage(UploadedFile $file): string
    {
        return $file->store(self::DIRECTORY, 'public');
    }

    /** Only paths this feature itself wrote are accepted from a form or a quiz builder. */
    public static function validImagePath(mixed $path): ?string
    {
        return is_string($path) && preg_match('#^' . self::DIRECTORY . '/[A-Za-z0-9._-]+$#', $path) ? $path : null;
    }

    public static function imageUrl(?string $path): ?string
    {
        return self::validImagePath($path) ? asset('storage/' . $path) : null;
    }

    /**
     * Clean a table coming from a form or a quiz builder: trims and limits the
     * cells, makes every row the same width, drops empty rows and columns.
     *
     * @return array{header: bool, total: bool, rows: list<list<string>>}|null  null when nothing is left
     */
    public static function sanitizeTable(mixed $raw): ?array
    {
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }
        if (! is_array($raw) || ! isset($raw['rows']) || ! is_array($raw['rows'])) {
            return null;
        }

        $rows = [];
        foreach (array_slice($raw['rows'], 0, self::MAX_ROWS) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $rows[] = array_map(
                fn ($cell) => mb_substr(trim(preg_replace('/\s+/u', ' ', is_scalar($cell) ? (string) $cell : '')), 0, self::MAX_CELL_LENGTH),
                array_slice(array_values($row), 0, self::MAX_COLUMNS),
            );
        }

        $width = $rows ? max(array_map('count', $rows)) : 0;
        $rows = array_map(fn ($row) => array_pad($row, $width, ''), $rows);

        // Drop rows with no content, then columns with no content.
        $rows = array_values(array_filter($rows, fn ($row) => implode('', $row) !== ''));
        if ($rows === []) {
            return null;
        }
        $keep = array_values(array_filter(range(0, $width - 1), fn ($c) => implode('', array_column($rows, $c)) !== ''));
        $rows = array_map(fn ($row) => array_map(fn ($c) => $row[$c], $keep), $rows);

        return [
            'header' => ! empty($raw['header']),
            'total' => ! empty($raw['total']) && count($rows) > 1,
            'rows' => $rows,
        ];
    }

    /** Whether a table cell reads as a number (right-aligned in the rendered table). */
    public static function isNumeric(string $cell): bool
    {
        return (bool) preg_match('/^[\(\-–]?\s*[₱$]?\s*[\d,]+(\.\d+)?\s*\)?\s*%?$/u', trim($cell));
    }

    /**
     * The exhibit columns of a question, only the ones that are set — so a
     * snapshot (mock exam item, quiz item, copied question) carries them along
     * without ever writing a null into a schema that lacks the columns.
     *
     * @return array<string, mixed>
     */
    public static function attributesOf(object $source): array
    {
        $attrs = [];
        if (self::validImagePath($source->image_path ?? null)) {
            $attrs['image_path'] = $source->image_path;
        }
        if (! empty($source->table_data)) {
            $attrs['table_data'] = $source->table_data;
        }

        return $attrs;
    }
}
