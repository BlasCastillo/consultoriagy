<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetaTrimestral extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'meta_trimestrals';

    protected $fillable = [
        'actividad_poa_id',
        'trimestre',
        'meta_inicial',
        'meta_actual',
        'ejecutada',
    ];

    public function actividadPoa()
    {
        return $this->belongsTo(ActividadPoa::class);
    }

    public function fichasActividades()
    {
        return $this->hasMany(FichaActividad::class);
    }

    public function evidencias()
    {
        return $this->hasMany(Evidencia::class);
    }
}
