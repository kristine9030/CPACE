<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One message in a Help & Support ticket thread, from either the requester
 * or the Program Chair.
 */
class IssueReportReply extends Model
{
    protected $fillable = ['issue_report_id', 'user_id', 'body'];

    public function report(): BelongsTo
    {
        return $this->belongsTo(IssueReport::class, 'issue_report_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
