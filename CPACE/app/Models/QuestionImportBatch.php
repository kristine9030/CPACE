<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One uploaded file being turned into Test Bank questions. See the
 * `question_import_tables` migration for the full status lifecycle.
 */
class QuestionImportBatch extends Model
{
    public const STATUS_PARSING   = 'parsing';
    public const STATUS_READY     = 'ready';
    public const STATUS_COMMITTED = 'committed';
    public const STATUS_FAILED    = 'failed';

    protected $fillable = [
        'faculty_id',
        'subject_id',
        'original_filename',
        'file_type',
        'status',
        'parse_source',
        'error_message',
    ];

    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function items()
    {
        return $this->hasMany(QuestionImportItem::class, 'batch_id')->orderBy('sort_order');
    }
}
