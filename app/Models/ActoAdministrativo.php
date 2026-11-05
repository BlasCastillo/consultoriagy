<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActoAdministrativo extends Model
{
    use SoftDeletes;

    protected $fillable = ['solicitud_id', 'tipo_acto', 'numero', 'sumario', 'archivo_pdf'];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    /**
     * URL pública del PDF escaneado de este acto administrativo.
     */
    public function getArchivoPdfUrlAttribute(): ?string
    {
        if (empty($this->archivo_pdf)) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->archivo_pdf);
    }
}
