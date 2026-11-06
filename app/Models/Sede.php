<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sede extends Model
{
    use SoftDeletes;

    protected $table = 'sedes';

    protected $fillable = ['nombre'];

    public function estantes()
    {
        return $this->hasMany(Estante::class);
    }
}
