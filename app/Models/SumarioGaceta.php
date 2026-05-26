<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SumarioGaceta extends Model
{
    protected $table = 'sumarios_gacetas';

    protected $fillable = [
        'gaceta_id',
        'institucion_id',
        'tipo_acto',
        'numero_acto',
        'descripcion',
    ];

    public function gaceta()
    {
        return $this->belongsTo(Gaceta::class);
    }

    public function institucion()
    {
        return $this->belongsTo(Institution::class);
    }
}
