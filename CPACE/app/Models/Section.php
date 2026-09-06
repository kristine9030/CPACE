<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected $casts = [
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
