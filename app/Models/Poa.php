<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poa extends Model
{
    use HasFactory;

    protected $fillable = [
        'anio',
        'jefatura_id',
        'estado',
    ];

    public static function checkAndClose(Poa $poa)
    {
        $poa->loadMissing('actividades.metasTrimestrales');
        
        $totalMeta = 0;
        $totalEjecutada = 0;

        foreach ($poa->actividades as $act) {
            foreach ($act->metasTrimestrales as $meta) {
                $totalMeta += $meta->meta_actual;
                $totalEjecutada += $meta->ejecutada;
            }
        }

        if ($totalMeta > 0 && $totalEjecutada >= $totalMeta) {
            $poa->update(['estado' => 'Cerrado']);
        }
    }

    public function jefatura()
    {
        return $this->belongsTo(Jefatura::class);
    }

    public function actividades()
    {
        return $this->hasMany(ActividadPoa::class);
    }
}
