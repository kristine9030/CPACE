<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'session_type',
        'mode',
        'subject_id',
        'topic_id',
        'started_at',
        'completed_at',
        'total_items',
        'correct_answers',
        'score_percent',
        'duration_secs',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function answers()
    {
        return $this->hasMany(QuizAnswer::class, 'session_id');
    }
}
