<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Gaceta extends Model
{
    use HasFactory, LogsActivity;

    const ESTADO_EN_PROCESO = 'En Proceso';
    const ESTADO_EN_DIGITALIZACION = 'En Digitalización';
    const ESTADO_DEVUELTA_DIGITALIZADOR = 'Devuelta a Digitalizador';
    const ESTADO_REVISION_FINAL = 'En Revisión Final';
    const ESTADO_PUBLICADA = 'Publicada';
    const ESTADO_RESERVADA = 'Reservada';
    const ESTADO_EN_ESCANEO = 'En Escaneo';
    const ESTADO_DEVUELTA = 'Devuelta';
    const ESTADO_POR_APROBAR = 'Por Aprobar';

    protected $fillable = [
        'numero',
        'anio',
        'tipo',
        'ano_politico',
        'mes_politico',
        'fecha_emision',
        'fecha_recepcion_fisica',
        'fecha_publicacion',
        'ruta_archivo',
        'archivo_pdf_completo',
        'estado',
        'corregida_de_id',
        'motivo_devolucion',
        'gobernador_id',
        'checklist',
        'jefe_id',
        'digitalizador_id',
        'ubicacion_topografica',
        'ejemplares_fisicos',
        'es_retroactiva',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_recepcion_fisica' => 'date',
        'fecha_publicacion' => 'date',
        'checklist' => 'array',
        'es_retroactiva' => 'boolean',
    ];

    public function prestamos()
    {
        return $this->hasMany(PrestamoArchivo::class);
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class)->orderBy('orden_sumario');
    }

    public function corregidaDe()
    {
        return $this->belongsTo(Gaceta::class, 'corregida_de_id');
    }

    public function correcciones()
    {
        return $this->hasMany(Gaceta::class, 'corregida_de_id');
    }

    public function gobernador()
    {
        return $this->belongsTo(Gobernador::class);
    }

    public function jefe()
    {
        return $this->belongsTo(User::class, 'jefe_id');
    }

    public function digitalizador()
    {
        return $this->belongsTo(User::class, 'digitalizador_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('BPM_Gacetas')
            ->setDescriptionForEvent(fn(string $eventName) => "La gaceta ha sido {$eventName}");
    }

    public function getDiasRetrasoAttribute()
    {
        if ($this->estado !== 'Publicada' && $this->fecha_recepcion_fisica) {
            $fechaRecepcion = \Carbon\Carbon::parse($this->fecha_recepcion_fisica);
            $hoy = \Carbon\Carbon::now();
            
            $diasHabiles = $fechaRecepcion->diffInDaysFiltered(function (\Carbon\Carbon $date) {
                return !$date->isWeekend();
            }, $hoy);

            if ($diasHabiles > 5) {
                return $diasHabiles;
            }
        }
        return 0;
    }
}
