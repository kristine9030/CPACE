<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacultyProfile extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'user_id';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'employee_number',
        'department',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
