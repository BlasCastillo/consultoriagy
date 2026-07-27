<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Replanificacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'actividad_poa_id',
        'trimestre_origen',
        'trimestre_destino',
        'cantidad_movida',
        'justificacion',
    ];

    public function actividadPoa()
    {
        return $this->belongsTo(ActividadPoa::class);
    }
}
