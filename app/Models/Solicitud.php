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
        'archivo_pdf_path',
        'estado',
        'gaceta_id',
        'orden_sumario',
        'checklist',
        'fecha_recepcion_fisica',
        'motivo_rechazo',
    ];

    protected $casts = [
        'checklist' => 'array',
        'fecha_recepcion_fisica' => 'datetime',
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

    public function actos()
    {
        return $this->hasMany(ActoAdministrativo::class);
    }

    public function getTiempoTranscurridoAttribute()
    {
        // Si hay fecha física se usa esa, sino el created_at del sistema
        $inicio = $this->fecha_recepcion_fisica ?? $this->created_at;

        // Si ya se procesó, el reloj se detiene en su updated_at. Si no, sigue corriendo hasta hoy.
        $fin = in_array($this->estado, ['Aprobada', 'Rechazada', 'Publicada']) ? $this->updated_at : now();

        if (!$inicio) return 'N/A';

        // Calcula la diferencia absoluta legible (Ej: "2 días 4 horas")
        return $inicio->diffForHumans($fin, [
            'parts' => 2,
            'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
        ]);
    }
}
