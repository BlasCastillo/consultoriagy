<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Titulo extends Model
{
    protected $fillable = ['abreviatura', 'descripcion'];

    public function gobernadores()
    {
        return $this->hasMany(Gobernador::class);
    }
}
