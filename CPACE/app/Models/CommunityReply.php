<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityReply extends Model
{
    protected $table = 'community_replies';

    // `community_replies` (seeded via cpace_database.sql) only has created_at.
    const UPDATED_AT = null;

    protected $fillable = ['post_id', 'author_id', 'body'];

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
