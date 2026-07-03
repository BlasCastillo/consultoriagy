<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestamoArchivo extends Model
{
    protected $table = 'prestamos_archivos';

    protected $fillable = [
        'gaceta_id',
        'solicitante_cedula',
        'solicitante_nombres',
        'telefono',
        'correo',
        'motivo_uso',
        'fecha_salida',
        'fecha_devolucion_estimada',
        'fecha_devolucion_real',
        'estado_prestamo',
    ];

    protected $casts = [
        'fecha_salida' => 'date',
        'fecha_devolucion_estimada' => 'date',
        'fecha_devolucion_real' => 'date',
    ];

    public function gaceta()
    {
        return $this->belongsTo(Gaceta::class);
    }
}
