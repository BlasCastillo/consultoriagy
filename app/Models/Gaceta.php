<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Gaceta extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'numero',
        'anio',
        'tipo',
        'anio_politico',
        'mes_politico',
        'fecha_emision',
        'fecha_recepcion_fisica',
        'fecha_publicacion',
        'ruta_archivo',
        'estado',
        'corregida_de_id',
        'gobernador_id',
        'checklist',
        'jefe_id',
        'digitalizador_id',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_recepcion_fisica' => 'date',
        'fecha_publicacion' => 'date',
        'checklist' => 'array',
    ];

    public function sumarios()
    {
        return $this->hasMany(SumarioGaceta::class);
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
