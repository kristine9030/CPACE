<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'student_number',
        'year_level',
        'section',
        'exam_target_date',
        'total_points',
        'streak_days',
        'mobile',
        'study_days',
        'study_time',
        'study_intensity',
        'focus_subjects',
    ];

    protected $casts = [
        'exam_target_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
