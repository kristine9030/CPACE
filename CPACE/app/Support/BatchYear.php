<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * A student's enrollment batch as a school-year label, e.g. "2026-2027".
 *
 * The Philippine school year opens in June, so anything from June of year Y
 * through May of year Y+1 belongs to batch "Y-(Y+1)".
 */
class BatchYear
{
    /** Month the school year starts (June). */
    public const START_MONTH = 6;

    public const PATTERN = '/^(\d{4})-(\d{4})$/';

    public static function forDate(CarbonInterface $date): string
    {
        $start = $date->month >= self::START_MONTH ? $date->year : $date->year - 1;

        return $start . '-' . ($start + 1);
    }

    public static function current(): string
    {
        return self::forDate(now());
    }

    /** True for "2026-2027"-shaped labels whose years are consecutive. */
    public static function isValid(?string $label): bool
    {
        return $label !== null
            && preg_match(self::PATTERN, $label, $m) === 1
            && (int) $m[2] === (int) $m[1] + 1;
    }

    /**
     * Whether student_profiles has the batch_year column yet (older hand-built
     * test schemas and not-yet-migrated databases don't). Memoised per request.
     */
    public static function columnExists(): bool
    {
        $key = 'cpace.batch_year_column';
        if (! app()->bound($key)) {
            app()->instance($key, \Illuminate\Support\Facades\Schema::hasColumn('student_profiles', 'batch_year'));
        }

        return app($key);
    }

    /** The batch immediately after $label ("2026-2027" -> "2027-2028"). */
    public static function next(string $label): ?string
    {
        if (! self::isValid($label)) {
            return null;
        }

        $start = (int) substr($label, 0, 4) + 1;

        return $start . '-' . ($start + 1);
    }

    /** The batch immediately before $label ("2026-2027" -> "2025-2026"). */
    public static function previous(string $label): ?string
    {
        if (! self::isValid($label)) {
            return null;
        }

        $start = (int) substr($label, 0, 4) - 1;

        return $start . '-' . ($start + 1);
    }
}
