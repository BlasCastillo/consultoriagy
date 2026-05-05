<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'rif',
        'type',
        'address',
        'phone',
        'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
