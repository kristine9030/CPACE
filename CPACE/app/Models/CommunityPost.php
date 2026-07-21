<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityPost extends Model
{
    // Pre-existing table (seeded via cpace_database.sql) — the Alumni
    // Community "group page" feed lives here.
    protected $fillable = [
        'author_id', 'subject_id', 'title', 'body', 'post_type', 'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function attachments()
    {
        return $this->hasMany(CommunityPostAttachment::class);
    }

    public function likes()
    {
        return $this->hasMany(CommunityPostLike::class);
    }

    public function replies()
    {
        return $this->hasMany(CommunityReply::class, 'post_id')->orderBy('created_at');
    }

    public function isLikedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->likes->contains('user_id', $user->id);
    }
}
