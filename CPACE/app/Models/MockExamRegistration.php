<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A student having redeemed a day's mock exam code.
 *
 * Registration is against the EVENT (the exam day), not an individual exam,
 * so a subject exam the Chair publishes later that same day is automatically
 * available to everyone who already redeemed - no backfill needed.
 */
class MockExamRegistration extends Model
{
    public $timestamps = false;

    protected $fillable = ['event_id', 'student_id', 'redeemed_at'];

    protected $casts = [
        'redeemed_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(MockExamEvent::class, 'event_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
