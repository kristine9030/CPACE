<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'question_id',
        'selected_choice',
        'is_correct',
        'answered_at',
    ];

    protected $casts = [
        'is_correct'  => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
