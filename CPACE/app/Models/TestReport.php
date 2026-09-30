<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestReport extends Model
{
    public $timestamps = false;

    public const TYPES = [
        'frontend' => 'Frontend Tests (Playwright)',
        'api' => 'API Tests (Postman)',
        'load' => 'Load Benchmark',
        'warehouse' => 'Data Warehouse Query Benchmark',
    ];

    protected $fillable = ['type', 'title', 'summary', 'raw_payload', 'uploaded_by', 'created_at'];

    protected function casts(): array
    {
        return [
            'summary' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
