<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One uploaded TOS PDF, staged for review before it becomes topics. */
class CurriculumImportBatch extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMMITTED = 'committed';

    protected $fillable = ['curriculum_version_id', 'created_by', 'original_filename', 'status'];

    public function items()
    {
        return $this->hasMany(CurriculumImportItem::class, 'batch_id')->orderBy('sort_order');
    }

    public function curriculumVersion()
    {
        return $this->belongsTo(CurriculumVersion::class);
    }
}
