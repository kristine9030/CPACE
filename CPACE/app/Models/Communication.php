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
}
