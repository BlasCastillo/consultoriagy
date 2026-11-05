<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Institucion extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'instituciones';

    protected $fillable = [
        'nombre',
        'siglas',
        'tipo_institucion_id',
        'estatus',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Esta institución ha sido {$eventName}");
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class);
    }

    public function tipoInstitucion()
    {
        return $this->belongsTo(TipoInstitucion::class, 'tipo_institucion_id');
    }

    /**
     * Nombre que se usa en el sumario de la Gaceta.
     * Elimina el "Yaracuy" final (ej. "Gobernación del estado Yaracuy" → "Gobernación del estado").
     */
    public function getNombreSumarioAttribute(): string
    {
        $nombre = trim((string) $this->nombre);

        if ($nombre === '') {
            return 'GOBERNACIÓN DEL ESTADO';
        }

        $nombre = preg_replace('/[\s,]+yaracuy\s*\.?\s*$/iu', '', $nombre) ?? $nombre;

        return trim($nombre);
    }

    /**
     * Nombre oficial completo. Si no incluye "Yaracuy", se le agrega
     * "del estado Yaracuy" para usarlo como título del acto.
     */
    public function getNombreOficialAttribute(): string
    {
        $nombre = trim((string) $this->nombre);

        if ($nombre === '') {
            return 'GOBERNACIÓN DEL ESTADO YARACUY';
        }

        if (stripos($nombre, 'yaracuy') === false) {
            $nombre .= ' del estado Yaracuy';
        }

        return $nombre;
    }

    /**
     * ¿Esta institución es la Gobernación (o el Despacho del Gobernador)?
     * En ese caso el título del acto corresponde al Gobernador del Estado.
     */
    public function getEsGobernacionAttribute(): bool
    {
        return (bool) preg_match('/gobernaci[oó]n|despacho\s+del\s+gobernador/iu', (string) $this->nombre);
    }

    /**
     * Líneas del título que se coloca en la primera hoja del PDF de un acto.
     * Para la Gobernación se usa el nombre y cargo del gobernador; para el
     * resto, el nombre oficial de la institución.
     *
     * @return array<int, string>
     */
    public function tituloActo(?object $gobernador = null): array
    {
        if ($this->es_gobernacion) {
            $nombre = trim(
                mb_strtoupper((string) ($gobernador?->titulo?->abreviatura ?? '')) . ' ' .
                mb_strtoupper((string) ($gobernador?->nombres ?? '')) . ' ' .
                mb_strtoupper((string) ($gobernador?->apellidos ?? ''))
            );

            return [
                $nombre !== '' ? $nombre : 'NO SE SELECCIONÓ GOBERNADOR',
                'GOBERNADOR DEL ESTADO YARACUY',
            ];
        }

        return [mb_strtoupper($this->nombre_oficial)];
    }
}
