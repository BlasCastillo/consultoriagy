<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TipoInstitucion extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'tipo_instituciones';
    protected $fillable = ['nombre'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function instituciones()
    {
        return $this->hasMany(Institucion::class, 'tipo_institucion_id');
    }
}
