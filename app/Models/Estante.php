<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estante extends Model
{
    use SoftDeletes;

    protected $fillable = ['sede_id', 'nombre'];

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function gavetas()
    {
        return $this->hasMany(Gaveta::class);
    }
}
