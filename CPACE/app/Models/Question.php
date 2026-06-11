<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'topic_id',
        'created_by',
        'question_text',
        'question_type',
        'difficulty',
        'explanation',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function choices()
    {
        return $this->hasMany(QuestionChoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
