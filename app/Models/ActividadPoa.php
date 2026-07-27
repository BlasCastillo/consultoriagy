<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActividadPoa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'poa_id',
        'descripcion',
        'partida_presupuestaria',
        'monto_asignado',
        'indicador_nombre',
        'unidad_medida',
        'medios_verificacion',
        'frecuencia_lectura',
        'formulacion',
    ];

    public function poa()
    {
        return $this->belongsTo(Poa::class);
    }

    public function metasTrimestrales()
    {
        return $this->hasMany(MetaTrimestral::class);
    }
}
