<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gaveta extends Model
{
    use SoftDeletes;

    protected $fillable = ['estante_id', 'nombre'];

    public function estante()
    {
        return $this->belongsTo(Estante::class);
    }
}
