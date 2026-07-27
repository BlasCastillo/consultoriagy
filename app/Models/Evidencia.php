<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evidencia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evidencias';

    protected $fillable = [
        'meta_trimestral_id',
        'archivo_path',
        'descripcion',
        'tipo',
    ];

    public function metaTrimestral()
    {
        return $this->belongsTo(MetaTrimestral::class);
    }
}
