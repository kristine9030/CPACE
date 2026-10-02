<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    /** Display labels for the curated 1-6 year_level range used across chair views. */
    public const YEAR_LABELS = [
        1 => '1st Year',
        2 => '2nd Year',
        3 => '3rd Year',
        4 => '4th Year',
        5 => '5th Year',
        6 => 'Irregular / 6th Year',
    ];

    /** Year levels a section code can encode (the "3" in BSA 3101). */
    public const CODE_YEAR_LEVELS = [1, 2, 3, 4];

    public const SEMESTER_LABELS = [
        1 => '1st Sem',
        2 => '2nd Sem',
    ];

    /**
     * Section naming format: "<PROGRAM> <year><semester><section no.>",
     * e.g. BSA 3101 = BSA, 3rd Year, 1st Sem, Section 01. Older names
     * (BSA-3A, ...) don't match and are left alone, never re-interpreted.
     */
    private const NAME_PATTERN = '/^([A-Z]{2,10}) ([1-4])([12])(\d{2})$/';

    protected $fillable = ['name', 'year_level', 'is_active'];

    protected $casts = [
        'year_level' => 'integer',
        'is_active' => 'boolean',
    ];

    public static function composeName(string $program, int $yearLevel, int $semester, int $sectionNumber): string
    {
        return strtoupper(trim($program)) . ' ' . $yearLevel . $semester . str_pad((string) $sectionNumber, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Split a name in the current format into its parts, or null for an
     * older/unsupported name (no semester or section number is guessed).
     *
     * @return array{program: string, year_level: int, semester: int, section_number: int}|null
     */
    public static function parseName(?string $name): ?array
    {
        if ($name === null || ! preg_match(self::NAME_PATTERN, $name, $m)) {
            return null;
        }

        return [
            'program' => $m[1],
            'year_level' => (int) $m[2],
            'semester' => (int) $m[3],
            'section_number' => (int) $m[4],
        ];
    }

    /** Semester encoded in the name (1 or 2), null for older-format names. */
    public function getSemesterAttribute(): ?int
    {
        return self::parseName($this->name)['semester'] ?? null;
    }

    public function getSemesterLabelAttribute(): ?string
    {
        return self::SEMESTER_LABELS[$this->semester] ?? null;
    }

    /**
     * Faculty members restricted to this section for one or more subjects.
     */
    public function faculty()
    {
        return $this->belongsToMany(User::class, 'faculty_subject_sections', 'section_id', 'faculty_id')
            ->withPivot('subject_id', 'assigned_by', 'assigned_at');
    }
}
