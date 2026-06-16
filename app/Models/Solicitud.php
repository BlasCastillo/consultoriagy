<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Solicitud extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'solicitudes';

    protected $fillable = [
        'institucion_id',
        'titulo_acto',
        'archivo_word_path',
        'estado',
        'gaceta_id',
        'orden_sumario',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Esta solicitud ha sido {$eventName}");
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class);
    }

    public function gaceta()
    {
        return $this->belongsTo(Gaceta::class);
    }
}
