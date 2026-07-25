<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioArchivo extends Model
{
    protected $table = 'inventarios_archivos';

    protected $fillable = [
        'gaceta_id',
        'sede_id',
        'estante_id',
        'gaveta_id',
        'stock',
        'observaciones',
    ];

    public function gaceta()
    {
        return $this->belongsTo(Gaceta::class);
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function estante()
    {
        return $this->belongsTo(Estante::class);
    }

    public function gaveta()
    {
        return $this->belongsTo(Gaveta::class);
    }
}
