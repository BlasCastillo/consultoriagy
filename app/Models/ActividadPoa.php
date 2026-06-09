<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadPoa extends Model
{
    use HasFactory;

    protected $fillable = [
        'poa_id',
        'descripcion',
        'partida_presupuestaria',
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
