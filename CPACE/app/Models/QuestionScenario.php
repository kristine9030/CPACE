<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A case problem in the Test Bank: one situation, any number of questions.
 * Quizzes and mock exams copy it (see ScenarioSupport) rather than point at it.
 */
class QuestionScenario extends Model
{
    protected $fillable = ['topic_id', 'created_by', 'title', 'body', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'scenario_id')->orderBy('scenario_position')->orderBy('id');
    }
}
