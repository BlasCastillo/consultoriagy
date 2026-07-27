<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Atencion extends Model
{
    protected $fillable = [
        'cedula', 
        'nombre', 
        'apellido', 
        'telefono', 
        'correo', 
        'institucion', 
        'jefatura_destino_id', 
        'motivo'
    ];

    public function jefaturaDestino()
    {
        return $this->belongsTo(Jefatura::class, 'jefatura_destino_id');
    }
}
