<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poa extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope('aislamiento_departamental', function (Builder $builder) {
            if (auth()->check()) {
                $usuario = auth()->user();
                
                if (!$usuario->hasAnyRole([
                    'Super Admin', 
                    'Director de Consultoría Jurídica', 
                    'Coordinador General', 
                    'Coordinador de Seguimiento y Control de Políticas Públicas'
                ])) {
                    $jefatura = $usuario->jefatura; // Carga la relación
                
                    if ($jefatura) {
                        $idsPermitidos = $jefatura->getDescendantsIds();
                        $builder->whereIn('jefatura_id', $idsPermitidos);
                    } else {
                        // Failsafe por si el usuario no tiene jefatura asignada
                        $builder->where('jefatura_id', $usuario->jefatura_id);
                    }
                }
            }
        });
    }

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

    public function metasTrimestrales()
    {
        return $this->hasManyThrough(MetaTrimestral::class, ActividadPoa::class);
    }
}
