<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Jefatura extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'nombre',
        'nivel',
        'parent_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Esta jefatura ha sido {$eventName}");
    }

    // La unidad superior a la que rinde cuentas
    public function unidadSuperior() {
        return $this->belongsTo(Jefatura::class, 'parent_id');
    }

    // Las unidades o cargos subordinados
    public function unidadesSubordinadas() {
        return $this->hasMany(Jefatura::class, 'parent_id');
    }

    // Usuarios asignados a este cargo/unidad
    public function usuarios() {
        return $this->hasMany(User::class, 'jefatura_id');
    }

    // Recursividad para árbol completo (PDF)
    public function childrenRecursive()
    {
        return $this->hasMany(Jefatura::class, 'parent_id')
                    ->orderBy('nivel', 'asc')
                    ->orderBy('nombre', 'asc')
                    ->with('childrenRecursive');
    }

    public function dependenciasHijas() {
        return $this->hasMany(Jefatura::class, 'parent_id'); 
    }

    public function getDescendantsIds() {
        $ids = [$this->id]; // Se incluye a sí mismo
        foreach ($this->dependenciasHijas as $hija) {
            $ids = array_merge($ids, $hija->getDescendantsIds());
        }
        return $ids;
    }
}
