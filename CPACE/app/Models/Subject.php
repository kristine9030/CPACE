<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    public $timestamps = false;

    protected $fillable = ['code', 'name', 'description'];

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
}
