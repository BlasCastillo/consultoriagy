<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FichaActividad extends Model
{
    use HasFactory;

    protected $fillable = [
        'meta_trimestral_id',
        'fecha',
        'titulo_actividad',
        'desarrollada_por',
        'cantidad_beneficiados',
        'instituciones_involucradas',
        'objetivo_logrado',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function metaTrimestral()
    {
        return $this->belongsTo(MetaTrimestral::class);
    }

    public function imagenes()
    {
        return $this->hasMany(ImagenFicha::class);
    }
}
