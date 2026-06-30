<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ApiToken extends Model
{
    protected $fillable = ['user_id', 'token', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public static function generate(int $userId): self
    {
        return self::create([
            'user_id'    => $userId,
            'token'      => bin2hex(random_bytes(64)),
            'expires_at' => Carbon::now()->addDays(30),
        ]);
    }
}
