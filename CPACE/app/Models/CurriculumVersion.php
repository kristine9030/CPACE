<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One generation of the CPALE curriculum (its topic trees across every
 * subject). At most one is ACTIVE — what students study and quizzes draw
 * from — and at most one is a DRAFT the chair is still building. Everything
 * older is ARCHIVED: read-only history whose topics are kept because
 * questions, performance records and quiz history still point at them.
 */
class CurriculumVersion extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    public $timestamps = false;

    protected $fillable = [
        'label', 'effective_from_batch', 'effective_to_batch', 'status',
        'created_by', 'published_at', 'created_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function audits()
    {
        return $this->hasMany(CurriculumAudit::class)->orderByDesc('id');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    /** Archived versions are history: nothing about them may change. */
    public function isEditable(): bool
    {
        return ! $this->isArchived();
    }

    /**
     * The curriculum that covers an enrollment batch: a published or archived
     * version whose range [effective_from_batch, effective_to_batch] contains
     * $batchYear. A draft never covers anyone — it isn't published yet.
     *
     * A NULL lower bound means "from the beginning" (the curriculum that
     * existed before versioning was added has no start batch), and a NULL
     * upper bound means "onward". Batch labels are all "YYYY-YYYY", so plain
     * string comparison orders them correctly.
     *
     * Returns null when nothing covers the batch; callers fall back to the
     * active curriculum so a student is never left with no topics.
     */
    public static function forBatch(?string $batchYear): ?self
    {
        if ($batchYear === null || $batchYear === '') {
            return null;
        }

        return static::whereIn('status', [self::STATUS_ACTIVE, self::STATUS_ARCHIVED])
            ->where(fn ($q) => $q->whereNull('effective_from_batch')->orWhere('effective_from_batch', '<=', $batchYear))
            ->where(fn ($q) => $q->whereNull('effective_to_batch')->orWhere('effective_to_batch', '>=', $batchYear))
            // If ranges ever overlap, the most recently started one wins.
            ->orderByRaw('effective_from_batch IS NULL')
            ->orderByDesc('effective_from_batch')
            ->orderByDesc('id')
            ->first();
    }

    /** "2026-2027 to 2028-2029", "2026-2027 onward", or null when unset. */
    public function effectiveRange(): ?string
    {
        if (! $this->effective_from_batch) {
            return null;
        }

        return $this->effective_to_batch
            ? "{$this->effective_from_batch} to {$this->effective_to_batch}"
            : "{$this->effective_from_batch} onward";
    }
}
