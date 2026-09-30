<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One support request: either a "Report an issue" submission from the landing
 * page footer (guest or signed in), or a ticket filed from Help & Support.
 */
class IssueReport extends Model
{
    public const STATUS_NEW       = 'new';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_RESOLVED  = 'resolved';

    public const STATUSES = [
        self::STATUS_NEW       => 'Open',
        self::STATUS_IN_REVIEW => 'In progress',
        self::STATUS_RESOLVED  => 'Resolved',
    ];

    /**
     * The categories offered in the form. The keys are stored; the labels are
     * what the form and the notification email show, so the two can never
     * drift apart.
     */
    public const CATEGORIES = [
        'bug'      => 'Something is broken',
        'account'  => 'Account or sign-in problem',
        'content'  => 'Wrong or unclear question content',
        'proctor'  => 'Proctoring or camera issue',
        'privacy'  => 'Data privacy concern',
        'other'    => 'Something else',
    ];

    protected $fillable = [
        'user_id', 'name', 'email', 'category', 'subject', 'message',
        'page_url', 'user_agent', 'ip_address', 'status',
        'resolved_at', 'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at'      => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(IssueReportReply::class)->orderBy('created_at')->orderBy('id');
    }

    /** Human label for the stored category key. */
    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES['other'];
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? self::STATUSES[self::STATUS_NEW];
    }

    /** Subject line, falling back to the category for landing-page reports. */
    public function title(): string
    {
        return $this->subject ?: $this->categoryLabel();
    }

    /** Who filed it, as the Support Inbox labels them. */
    public function requesterRoleLabel(): string
    {
        $user = $this->user;

        return match (true) {
            $user === null     => $this->user_id ? 'Former user' : 'Guest',
            $user->isChair()   => 'Program Chair',
            $user->isFaculty() => 'Faculty',
            $user->isAlumni()  => 'Alumni',
            default            => 'Student',
        };
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    /** The requester, or the Program Chair who handles every ticket. */
    public function isVisibleTo(User $user): bool
    {
        return $user->isChair() || ($this->user_id !== null && (int) $this->user_id === (int) $user->id);
    }
}
