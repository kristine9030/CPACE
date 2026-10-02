<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Communication extends Model
{
    protected $fillable = [
        'sender_id', 'audience', 'target_type', 'target_filters', 'title',
        'message', 'type', 'priority', 'link', 'recipient_count',
    ];

    protected function casts(): array
    {
        return ['target_filters' => 'array', 'recipient_count' => 'integer'];
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /** Who it went to, in a few words: "Everyone", "Year 3 · BSA 3101", "FAR", "Chosen people". */
    public function targetSummary(): string
    {
        if ($this->target_type === 'all') {
            return 'Everyone';
        }
        if ($this->target_type === 'selected') {
            return 'Chosen people';
        }

        $filters = (array) $this->target_filters;
        $parts = [];
        if (! empty($filters['year_level'])) {
            $parts[] = 'Year ' . $filters['year_level'];
        }
        if (! empty($filters['section'])) {
            $parts[] = $filters['section'];
        }
        if (! empty($filters['subject_id'])) {
            $parts[] = Subject::find((int) $filters['subject_id'])?->code ?? 'Subject';
        }

        return $parts ? implode(' · ', $parts) : 'A group';
    }

    public function attachments()
    {
        return $this->hasMany(CommunicationAttachment::class);
    }
}
