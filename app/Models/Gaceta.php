<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Gaceta extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'gacetas';

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
        'pdf_final',
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

    /**
     * Calcula el periodo político (año y mes) a partir de una fecha.
     *
     * Regla de mes: cada periodo inicia el día 10 y finaliza el día 9.
     *   I:   10 Sep - 09 Oct, II:  10 Oct - 09 Nov, III: 10 Nov - 09 Dec,
     *   IV:  10 Dec - 09 Ene, V:   10 Ene - 09 Feb, VI:  10 Feb - 09 Mar,
     *   VII: 10 Mar - 09 Abr, VIII:10 Abr - 09 May, IX:  10 May - 09 Jun,
     *   X:   10 Jun - 09 Jul, XI:  10 Jul - 09 Ago, XII: 10 Ago - 09 Sep.
     *
     * Regla de año: periodo 10 Sep - 09 Sep. El periodo iniciado el 10 Sep 2026
     * corresponde a CXVII (117).
     */
    public static function calcularPeriodoPolitico($fecha): array
    {
        $date = $fecha instanceof \Carbon\Carbon
            ? $fecha->copy()
            : \Carbon\Carbon::parse($fecha);

        $day = (int) $date->day;
        $month = (int) $date->month;
        $year = (int) $date->year;

        $mesClave = $day >= 10 ? $month : ($month === 1 ? 12 : $month - 1);
        $mapaMes = [
            9  => 'I',   // 10 Sep - 09 Oct
            10 => 'II',  // 10 Oct - 09 Nov
            11 => 'III', // 10 Nov - 09 Dec
            12 => 'IV',  // 10 Dec - 09 Ene
            1  => 'V',   // 10 Ene - 09 Feb
            2  => 'VI',  // 10 Feb - 09 Mar
            3  => 'VII', // 10 Mar - 09 Abr
            4  => 'VIII',// 10 Abr - 09 May
            5  => 'IX',  // 10 May - 09 Jun
            6  => 'X',   // 10 Jun - 09 Jul
            7  => 'XI',  // 10 Jul - 09 Ago
            8  => 'XII', // 10 Ago - 09 Sep
        ];

        $inicioPeriodo = ($month > 9 || ($month === 9 && $day >= 10)) ? $year : ($year - 1);
        $numeroAno = $inicioPeriodo - 1909; // 2026 -> CXVII

        return [
            'ano_politico' => self::numeroARomano((string) max($numeroAno, 1)),
            'mes_politico' => $mapaMes[$mesClave] ?? 'I',
            'numero_ano'   => $numeroAno,
        ];
    }

    public static function calcularAnoPolitico($fecha): string
    {
        return self::calcularPeriodoPolitico($fecha)['ano_politico'];
    }

    public static function calcularMesPolitico($fecha): string
    {
        return self::calcularPeriodoPolitico($fecha)['mes_politico'];
    }

    public static function numeroARomano($valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        $str = trim((string) $valor);
        $clean = preg_replace('/^(a[ñn]o|mes)\s*/iu', '', $str) ?? $str;
        $clean = trim(str_replace(['°', 'º', '.', ',', '-', '_'], '', $clean));

        // 1. Si tiene números arábigos, convertirlos a numeración romana
        if (preg_match('/\d+/', $clean, $matches)) {
            $num = (int) $matches[0];
            if ($num > 0 && $num < 4000) {
                $map = [
                    'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
                    'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
                    'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1,
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

        // 2. Si el usuario escribió el nombre del mes en español, convertirlo a romano (I - XII)
        $mesesTexto = [
            'enero' => 'I',
            'febrero' => 'II',
            'marzo' => 'III',
            'abril' => 'IV',
            'mayo' => 'V',
            'junio' => 'VI',
            'julio' => 'VII',
            'agosto' => 'VIII',
            'septiembre' => 'IX',
            'setiembre' => 'IX',
            'octubre' => 'X',
            'noviembre' => 'XI',
            'diciembre' => 'XII',
        ];
        $cleanLower = mb_strtolower($clean, 'UTF-8');
        if (isset($mesesTexto[$cleanLower])) {
            return $mesesTexto[$cleanLower];
        }

        // 3. Validar si ya es un número romano canónico estricto (1 a 3999)
        if ($clean !== '' && preg_match('/^M{0,3}(CM|CD|D?C{0,3})(XC|XL|L?X{0,3})(IX|IV|V?I{0,3})$/i', $clean)) {
            return strtoupper($clean);
        }

        return strtoupper($clean);
    }

    public function setAnoPoliticoAttribute($value): void
    {
        $this->attributes['ano_politico'] = $value !== null ? self::numeroARomano($value) : null;
    }

    public function setMesPoliticoAttribute($value): void
    {
        $this->attributes['mes_politico'] = $value !== null ? self::numeroARomano($value) : null;
    }

    public function getAnioPoliticoAttribute()
    {
        return $this->attributes['ano_politico'] ?? null;
    }

    public function setAnioPoliticoAttribute($value): void
    {
        $this->setAnoPoliticoAttribute($value);
    }

    public function getAnoPoliticoRomanoAttribute(): string
    {
        return self::numeroARomano($this->attributes['ano_politico'] ?? '');
    }

    public function getAnioPoliticoRomanoAttribute(): string
    {
        return $this->ano_politico_romano;
    }

    public function getMesPoliticoRomanoAttribute(): string
    {
        return self::numeroARomano($this->attributes['mes_politico'] ?? '');
    }

    public function getTextoPoliticoAttribute(): string
    {
        $ano = $this->ano_politico_romano ?: 'CXVII';
        $mes = $this->mes_politico_romano ?: 'I';
        return "AÑO {$ano} - MES {$mes}";
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
