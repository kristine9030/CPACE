<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewNote extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'topic_id',
        'title',
        'content',
        'tags',
        'is_favorite',
        'review_count',
        'last_reviewed_at',
    ];

    protected $casts = [
        'is_favorite'      => 'boolean',
        'review_count'     => 'integer',
        'last_reviewed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * The note's tags as a clean array (split on commas, trimmed, empties dropped).
     */
    public function tagList(): array
    {
        if (blank($this->tags)) {
            return [];
        }

        return collect(explode(',', $this->tags))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->all();
    }
}
