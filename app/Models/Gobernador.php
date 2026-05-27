<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gobernador extends Model
{
    protected $table = 'gobernadores';

    protected $fillable = ['titulo_id', 'nombres', 'apellidos', 'estado'];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function titulo()
    {
        return $this->belongsTo(Titulo::class);
    }

    public function gacetas()
    {
        return $this->hasMany(Gaceta::class);
    }
}
