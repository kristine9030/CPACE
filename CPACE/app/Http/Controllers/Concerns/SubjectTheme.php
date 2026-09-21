<?php

namespace App\Http\Controllers\Concerns;

/**
 * The subject colour/icon identity used on subject cards across the app, so a
 * subject reads as the same colour and icon wherever it appears.
 *
 * Extracted from Faculty\FacultyQuizController, which still carries its own
 * copy; that controller and Student\ClassQuizController can adopt this trait
 * whenever they're next touched.
 */
trait SubjectTheme
{
    private const SUBJECT_COLORS = [
        'FAR' => '#4A90E2', 'AFAR' => '#17A2B8', 'MS' => '#F39C12',
        'TAX' => '#27AE60', 'AUD' => '#c0392b', 'RFBT' => '#9B59B6',
    ];

    private const SUBJECT_ICONS = [
        'FAR' => 'fa-chart-line', 'AFAR' => 'fa-coins', 'MS' => 'fa-gears',
        'TAX' => 'fa-file-invoice-dollar', 'AUD' => 'fa-magnifying-glass', 'RFBT' => 'fa-scale-balanced',
    ];

    protected static function subjectIcon(?string $code): string
    {
        return self::SUBJECT_ICONS[strtoupper((string) $code)] ?? 'fa-layer-group';
    }

    /** @return array{base:string,dark:string,pastel:string,soft:string} */
    protected static function theme(?string $code): array
    {
        $base = self::SUBJECT_COLORS[strtoupper((string) $code)] ?? '#7B1D1D';

        return [
            'base' => $base,
            'dark' => self::darken($base, 0.32),
            'pastel' => self::tint($base, 0.88),
            'soft' => self::tint($base, 0.72),
        ];
    }

    protected static function darken(string $hex, float $amount): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return sprintf('#%02x%02x%02x', $r * (1 - $amount), $g * (1 - $amount), $b * (1 - $amount));
    }

    protected static function tint(string $hex, float $amount): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return sprintf('#%02x%02x%02x', $r + (255 - $r) * $amount, $g + (255 - $g) * $amount, $b + (255 - $b) * $amount);
    }
}
