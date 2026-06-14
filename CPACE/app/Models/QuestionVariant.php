<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An alternative phrasing of a Question, used at display time so students see
 * the same question worded differently across attempts. Same meaning, same
 * correct answer - only the wording differs.
 */
class QuestionVariant extends Model
{
    protected $fillable = [
        'question_id',
        'variant_text',
        'source',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
