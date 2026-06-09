<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Replanificacion extends Model
{
    use HasFactory;

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
