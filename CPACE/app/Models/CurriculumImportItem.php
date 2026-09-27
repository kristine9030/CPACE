<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One parsed TOS heading awaiting the chair's review. */
class CurriculumImportItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'batch_id', 'subject_id', 'parent_item_id', 'ref', 'name', 'full_name',
        'depth', 'weight_percent', 'item_count', 'sort_order', 'included',
    ];

    protected $casts = [
        'depth' => 'integer',
        'weight_percent' => 'float',
        'item_count' => 'integer',
        'sort_order' => 'integer',
        'included' => 'boolean',
    ];
}
