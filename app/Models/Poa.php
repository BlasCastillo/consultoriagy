<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'poas';

    public static function usuarioTieneAccesoGlobal($usuario): bool
    {
        if (!$usuario) {
            return false;
        }

        return $usuario->hasAnyRole([
            'Super Admin',
            'Super Administrador',
            'Súper Administrador',
            'Director de Consultoría Jurídica',
            'Coordinador General',
            'Coordinador de Seguimiento y Control de Políticas Públicas',
        ]);
    }

    public static function obtenerJefaturasPermitidas($usuario): array
    {
        if (!$usuario) {
            return [];
        }

        $idsPermitidos = [];
        $jefatura = $usuario->jefatura;

        if ($jefatura) {
            $idsPermitidos = array_merge($idsPermitidos, $jefatura->getDescendantsIds());
        } elseif ($usuario->jefatura_id) {
            $idsPermitidos[] = (int) $usuario->jefatura_id;
        }

        $rolesUsuario = $usuario->roles->pluck('name')->toArray();
        foreach ($rolesUsuario as $roleName) {
            if ($roleName === 'Coordinador de Publicaciones Oficiales') {
                $ids = Jefatura::where('nombre', 'ILIKE', '%Publicaciones%')->pluck('id')->toArray();
                $idsPermitidos = array_merge($idsPermitidos, $ids);
            } elseif ($roleName === 'Coordinador de Archivo') {
                $ids = Jefatura::where('nombre', 'ILIKE', '%Archivo%')->pluck('id')->toArray();
                $idsPermitidos = array_merge($idsPermitidos, $ids);
            } elseif ($roleName === 'Coordinador de Atención al Ciudadano') {
                $ids = Jefatura::where('nombre', 'ILIKE', '%Atención%')->pluck('id')->toArray();
                $idsPermitidos = array_merge($idsPermitidos, $ids);
            } elseif (in_array($roleName, [
                'Coordinador de Legislación y Asuntos Jurídicos',
                'Jefe de Legislación',
                'Jefe de Asuntos Jurídicos',
            ], true)) {
                $ids = Jefatura::where('nombre', 'ILIKE', '%Legislación%')
                    ->orWhere('nombre', 'ILIKE', '%Asuntos Jurídicos%')
                    ->pluck('id')
                    ->toArray();
                $idsPermitidos = array_merge($idsPermitidos, $ids);
            }
        }

        return array_values(array_unique(array_map('intval', $idsPermitidos)));
    }

    protected static function booted()
    {
        static::addGlobalScope('aislamiento_departamental', function (Builder $builder) {
            if (auth()->check()) {
                $usuario = auth()->user();

                if (!self::usuarioTieneAccesoGlobal($usuario)) {
                    $idsPermitidos = self::obtenerJefaturasPermitidas($usuario);

                    if (!empty($idsPermitidos)) {
                        $builder->whereIn('jefatura_id', $idsPermitidos);
                    } else {
                        $builder->whereIn('jefatura_id', [0]);
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
        $poa->load('actividades.metasTrimestrales');

        $totalMeta = 0;
        $totalEjecutada = 0;

        foreach ($poa->actividades as $act) {
            foreach ($act->metasTrimestrales as $meta) {
                $totalMeta += $meta->meta_actual;
                $totalEjecutada += $meta->ejecutada;
            }
        }

        if ($totalMeta > 0 && $totalEjecutada >= $totalMeta) {
            if ($poa->estado !== 'Cerrado') {
                $poa->update(['estado' => 'Cerrado']);
            }
        } elseif ($poa->estado === 'Cerrado' && ($totalMeta === 0 || $totalEjecutada < $totalMeta)) {
            $poa->update(['estado' => 'Ejecucion']);
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
