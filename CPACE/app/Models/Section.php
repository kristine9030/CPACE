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

    protected $fillable = ['name', 'year_level', 'is_active'];

    protected $casts = [
        'year_level' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Faculty members restricted to this section for one or more subjects.
     */
    public function faculty()
    {
        return $this->belongsToMany(User::class, 'faculty_subject_sections', 'section_id', 'faculty_id')
            ->withPivot('subject_id', 'assigned_by', 'assigned_at');
    }
}
