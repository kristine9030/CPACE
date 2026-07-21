<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniProfile extends Model
{
    // `alumni_profiles` is keyed by user_id (no auto-increment id column)
    // and has no created_at/updated_at columns — seeded via cpace_database.sql.
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'batch_year', 'cpa_number', 'passed_at',
        'current_job', 'company', 'linkedin_url', 'bio',
    ];

    protected $casts = [
        'passed_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
