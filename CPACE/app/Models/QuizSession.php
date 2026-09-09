<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizSession extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'session_type',
        'is_practice_room',
        'practice_difficulty',
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
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
        'is_practice_room' => 'boolean',
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
