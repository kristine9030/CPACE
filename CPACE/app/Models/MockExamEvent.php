<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * One mock exam sitting DAY, and the single redeem code that opens every
 * subject exam scheduled on it. Created the first time the Program Chair
 * publishes an exam for a given date; every later publish for that same date
 * reuses the same row, so students only ever redeem one code per exam day.
 */
class MockExamEvent extends Model
{
    protected $fillable = ['exam_date', 'access_code', 'created_by'];

    protected $casts = [
        'exam_date' => 'date',
    ];

    public function exams()
    {
        return $this->hasMany(MockExam::class, 'event_id');
    }

    public function registrations()
    {
        return $this->hasMany(MockExamRegistration::class, 'event_id');
    }

    /**
     * Find the day's event or create it with a fresh code.
     *
     * The code embeds the date so it reads naturally when dictated in class,
     * but carries a random suffix as well - a pure date code would be
     * trivially guessable by anyone who knows when the exam is.
     */
    public static function forDate(Carbon $date, ?int $createdBy = null): self
    {
        $event = static::whereDate('exam_date', $date->toDateString())->first();
        if ($event) {
            return $event;
        }

        return static::create([
            'exam_date' => $date->toDateString(),
            'access_code' => static::newAccessCode($date),
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Alphabet with no O/0 or I/1, so the code survives being read out loud
     * and copied off a projector.
     */
    private const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function newAccessCode(Carbon $date): string
    {
        do {
            $suffix = '';
            for ($i = 0; $i < 4; $i++) {
                $suffix .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
            }
            $code = 'MOCK-' . $date->format('Ymd') . '-' . $suffix;
        } while (static::where('access_code', $code)->exists());

        return $code;
    }

    /** Normalises whatever the student typed before we look it up. */
    public static function normaliseCode(string $raw): string
    {
        return Str::upper(trim(preg_replace('/\s+/', '', $raw)));
    }
}
