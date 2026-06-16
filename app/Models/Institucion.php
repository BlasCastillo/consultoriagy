<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Institucion extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'instituciones';

    protected $fillable = [
        'nombre',
        'siglas',
        'tipo_institucion_id',
        'estatus',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Esta institución ha sido {$eventName}");
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class);
    }

    public function tipoInstitucion()
    {
        return $this->belongsTo(TipoInstitucion::class, 'tipo_institucion_id');
    }
}
