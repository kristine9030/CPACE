<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    // Role IDs as seeded in cpace_database.sql
    public const ADMIN = 1;
    public const STUDENT = 2;
    public const FACULTY = 3;
    public const ALUMNI = 4;

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
