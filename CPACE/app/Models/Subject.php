<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    public $timestamps = false;

    protected $fillable = ['code', 'name', 'description'];

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * Faculty members assigned to this subject by the Program Chair.
     */
    public function faculty()
    {
        return $this->belongsToMany(User::class, 'faculty_subjects', 'subject_id', 'faculty_id')
            ->withPivot('assigned_by', 'assigned_at');
    }
}
