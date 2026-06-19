<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActoAdministrativo extends Model
{
    use SoftDeletes;

    protected $fillable = ['solicitud_id', 'tipo_acto', 'numero', 'sumario'];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }
}
