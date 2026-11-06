<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImagenFicha extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'imagen_fichas';

    protected $fillable = [
        'ficha_actividad_id',
        'ruta_imagen',
    ];

    public function fichaActividad()
    {
        return $this->belongsTo(FichaActividad::class);
    }
}
