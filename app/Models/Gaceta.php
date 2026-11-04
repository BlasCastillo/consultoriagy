<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Gaceta extends Model
{
    use HasFactory, LogsActivity;

    const ESTADO_EN_PROCESO = 'En Proceso';
    const ESTADO_EN_DIGITALIZACION = 'En Digitalización';
    const ESTADO_DEVUELTA_DIGITALIZADOR = 'Devuelta a Digitalizador';
    const ESTADO_REVISION_FINAL = 'En Revisión Final';
    const ESTADO_PUBLICADA = 'Publicada';
    const ESTADO_RESERVADA = 'Reservada';
    const ESTADO_EN_ESCANEO = 'En Escaneo';
    const ESTADO_DEVUELTA = 'Devuelta';
    const ESTADO_POR_APROBAR = 'Por Aprobar';

    protected $fillable = [
        'numero',
        'anio',
        'tipo',
        'ano_politico',
        'mes_politico',
        'fecha_emision',
        'fecha_recepcion_fisica',
        'fecha_publicacion',
        'ruta_archivo',
        'archivo_pdf_completo',
        'estado',
        'corregida_de_id',
        'motivo_devolucion',
        'gobernador_id',
        'checklist',
        'jefe_id',
        'digitalizador_id',
        'ubicacion_topografica',
        'ejemplares_fisicos',
        'es_retroactiva',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_recepcion_fisica' => 'date',
        'fecha_publicacion' => 'date',
        'checklist' => 'array',
        'es_retroactiva' => 'boolean',
    ];

    public function prestamos()
    {
        return $this->hasMany(PrestamoArchivo::class);
    }

    public function inventario()
    {
        return $this->hasOne(InventarioArchivo::class);
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class)->orderBy('orden_sumario');
    }

    public function corregidaDe()
    {
        return $this->belongsTo(Gaceta::class, 'corregida_de_id');
    }

    public function correcciones()
    {
        return $this->hasMany(Gaceta::class, 'corregida_de_id');
    }

    public function gobernador()
    {
        return $this->belongsTo(Gobernador::class);
    }

    public function jefe()
    {
        return $this->belongsTo(User::class, 'jefe_id');
    }

    public function digitalizador()
    {
        return $this->belongsTo(User::class, 'digitalizador_id');
    }

    public static function numeroARomano($valor): string
    {
        if (empty($valor)) return '';
        $str = trim((string)$valor);
        $clean = preg_replace('/^(a[ñn]o|mes)\s*/iu', '', $str);
        $clean = trim(str_replace(['°', 'º'], '', $clean));
        if (preg_match('/^[IVXLCDM]+$/i', $clean)) {
            return strtoupper($clean);
        }
        if (preg_match('/\d+/', $clean, $matches)) {
            $num = (int)$matches[0];
            if ($num > 0 && $num < 4000) {
                $map = [
                    'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
                    'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
                    'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
                ];
                $roman = '';
                foreach ($map as $rom => $v) {
                    while ($num >= $v) {
                        $roman .= $rom;
                        $num -= $v;
                    }
                }
                return $roman;
            }
        }
        return strtoupper($str);
    }

    public function getAnioPoliticoAttribute()
    {
        return $this->attributes['ano_politico'] ?? null;
    }

    public function setAnioPoliticoAttribute($value)
    {
        $this->attributes['ano_politico'] = $value;
    }

    public function getAnoPoliticoRomanoAttribute(): string
    {
        return self::numeroARomano($this->ano_politico);
    }

    public function getAnioPoliticoRomanoAttribute(): string
    {
        return $this->ano_politico_romano;
    }

    public function getMesPoliticoRomanoAttribute(): string
    {
        return self::numeroARomano($this->mes_politico);
    }

    public function getTextoPoliticoAttribute(): string
    {
        $ano = $this->ano_politico_romano ?: 'CXIV';
        $mes = $this->mes_politico_romano ?: 'IV';
        return "AÑO {$ano} – MES {$mes}";
    }

    public function getFechaParaMostrarAttribute()
    {
        return $this->fecha_emision ?? $this->fecha_publicacion;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('BPM_Gacetas')
            ->setDescriptionForEvent(fn(string $eventName) => "La gaceta ha sido {$eventName}");
    }

    public function getDiasRetrasoAttribute()
    {
        if ($this->estado !== 'Publicada' && $this->fecha_recepcion_fisica) {
            $fechaRecepcion = \Carbon\Carbon::parse($this->fecha_recepcion_fisica);
            $hoy = \Carbon\Carbon::now();
            
            $diasHabiles = $fechaRecepcion->diffInDaysFiltered(function (\Carbon\Carbon $date) {
                return !$date->isWeekend();
            }, $hoy);

            if ($diasHabiles > 5) {
                return $diasHabiles;
            }
        }
        return 0;
    }
}
