<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ApiToken extends Model
{
    protected $fillable = ['user_id', 'name', 'token', 'expires_at', 'last_used_at'];

    protected $casts = ['expires_at' => 'datetime', 'last_used_at' => 'datetime'];

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

    /**
     * A named, admin-issued token for automation (CI uploading a test
     * report, a scheduled benchmark, ...) — as opposed to a mobile-app
     * login token, which is unnamed and always expires in 30 days.
     * $expiresInDays = null means the token never expires.
     */
    public static function issueNamed(int $userId, string $name, ?int $expiresInDays = null): self
    {
        return self::create([
            'user_id'    => $userId,
            'name'       => $name,
            'token'      => bin2hex(random_bytes(64)),
            'expires_at' => $expiresInDays !== null ? Carbon::now()->addDays($expiresInDays) : null,
        ]);
    }
}
