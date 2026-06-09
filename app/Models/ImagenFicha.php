<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagenFicha extends Model
{
    use HasFactory;

    protected $fillable = [
        'ficha_actividad_id',
        'ruta_imagen',
    ];

    public function fichaActividad()
    {
        return $this->belongsTo(FichaActividad::class);
    }
}
