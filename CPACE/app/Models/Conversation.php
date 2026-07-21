<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['type', 'name', 'is_default_group', 'created_by'];

    protected $casts = [
        'is_default_group' => 'boolean',
    ];

    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot('joined_at', 'last_read_at');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Find (or lazily create) the single default group every student + alumnus belongs to. */
    public static function defaultGroup(): self
    {
        return static::firstOrCreate(
            ['is_default_group' => true],
            ['type' => 'group', 'name' => 'CPACE Community']
        );
    }

    /** Add a user to this conversation if they aren't already in it. */
    public function addParticipant(User $user): void
    {
        if ($this->participants()->where('users.id', $user->id)->exists()) {
            return;
        }

        $this->participants()->attach($user->id, ['joined_at' => now()]);
    }

    /** Find the existing 1-on-1 conversation between two users, if any. */
    public static function findDirectBetween(int $userIdA, int $userIdB): ?self
    {
        return static::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('users.id', $userIdA))
            ->whereHas('participants', fn ($q) => $q->where('users.id', $userIdB))
            ->withCount('participants')
            ->get()
            ->first(fn (self $c) => $c->participants_count === 2);
    }

    /** Display name for this conversation from a specific viewer's perspective. */
    public function displayNameFor(User $viewer): string
    {
        if ($this->type === 'group') {
            return $this->name ?? 'Group Chat';
        }

        $other = $this->participants->firstWhere('id', '!=', $viewer->id);

        return $other?->name ?? 'Unknown User';
    }

    public function unreadCountFor(User $user): int
    {
        $pivot = $this->participants->firstWhere('id', $user->id)?->pivot;
        $lastRead = $pivot?->last_read_at;

        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->when($lastRead, fn ($q) => $q->where('created_at', '>', $lastRead))
            ->count();
    }
}
