{{-- ── PORTADA + SUMARIO (fuente única de verdad) ────────────────────────
     Se usa tanto en la Vista Previa Legal (show/revisión/digitalización)
     como en el PDF generado con DomPDF, para que ambos se vean idénticos.

     Requiere: $gaceta con solicitudes.institucion y solicitudes.actos.
     No incluye el contenedor del acto: en gacetas con varios actos ese va
     fuera de la portada.
     ──────────────────────────────────────────────────────────────────── --}}
<style>
    .gacetahoja {
        width: 100%;
        max-width: 215.9mm;
        box-sizing: border-box;
        padding: 48px 64px 32px 64px;
        background: #fff;
        color: #000;
        font-family: sans-serif;
    }

    .gacetahoja .titulo {
        text-align: center;
        font-family: sans-serif;
        font-weight: 900;
        font-size: 80px;
        line-height: 0.85;
        white-space: nowrap;
        margin: 0 0 -10px 0;
    }

    .gacetahoja .subtitulo {
        text-align: center;
        font-family: serif;
        font-size: 27.2px;
        font-weight: bold;
        letter-spacing: 0.18em;
        white-space: nowrap;
        margin: 0 0 4px 0;
    }

    .gacetahoja .separador-grueso {
        border-top: 4px solid #000;
        width: 100%;
        margin: 4px 0;
    }

    .gacetahoja .separador {
        border-top: 3px solid #000;
        width: 100%;
        margin: 4px 0;
    }

    .gacetahoja .legal {
        font-family: sans-serif;
        font-size: 9.92px;
        line-height: 1.25;
        text-align: justify;
        margin: 0 0 4px 0;
    }

    .gacetahoja .info {
        width: 100%;
        font-family: sans-serif;
        font-weight: bold;
        font-size: 15.2px;
        text-transform: uppercase;
        margin: 4px 0;
        border-collapse: collapse;
    }

    .gacetahoja .info td {
        vertical-align: bottom;
        padding: 0;
    }

    .gacetahoja .col-izq { text-align: left; width: 33%; }
    .gacetahoja .col-cen { text-align: center; width: 34%; }
    .gacetahoja .col-der {
        text-align: right;
        width: 33%;
        font-size: 18px;
        letter-spacing: 2px;
    }

    .gacetahoja .separador-sumario {
        border-top: 3px solid #000;
        width: 100%;
        margin: 4px 0 16px 0;
    }

    .gacetahoja .sumario-titulo {
        text-align: center;
        font-family: sans-serif;
        font-weight: bold;
        font-size: 18px;
        letter-spacing: 0.2em;
        margin: 0 0 16px 0;
    }

    .gacetahoja .institucion {
        text-align: center;
        font-weight: bold;
        font-size: 16px;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin: 0 0 16px 0;
    }

    .gacetahoja .acto {
        font-family: sans-serif;
        font-size: 13.6px;
        line-height: 1.625;
        text-align: justify;
        margin: 0 0 16px 0;
        page-break-inside: avoid;
    }

    .gacetahoja .acto-titulo {
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0 0 2px 0;
    }

    .gacetahoja .acto-sumario {
        font-weight: normal;
        margin: 0;
    }

    .gacetahoja .linea-final {
        border-top: 4px solid #000;
        width: 100%;
        margin: 24px 0 8px 0;
    }
</style>

<div id="hoja-portada" class="gacetahoja shadow-2xl" style="min-height: 279.4mm;">
    <div class="titulo">GACETA OFICIAL</div>
    <div class="subtitulo">DEL ESTADO YARACUY</div>

    <div class="separador-grueso"></div>

    <div class="legal">
        Los actos de los Poderes Públicos y aquellos cuya inclusión sea conveniente por el Ejecutivo Estadal
        o los demás órganos o Entes de la Administración Pública Estadal, que registre la Gaceta Oficial del
        Estado, tendrán fuerza de documento público desde que aparezcan publicados en ella, artículo 2º del
        Decreto del 10 de septiembre de 1.909 publicado en Gaceta Oficial del Edo. Yaracuy N° 01 del
        11/09/1.909, y en cumplimiento con lo establecido en los Artículos 11, 12, 13 y 14 de la Ley N° 2-
        Ley de Publicaciones Oficiales del Estado Yaracuy, Gaceta Oficial N° 2.565 del 23/01/2003.
        (Impresión en papel Bond por autorización según resolución N° 001 de la secretaria de Despacho, de
        fecha 13 de septiembre de 2013).
    </div>

    <div class="separador"></div>

    <table class="info">
        <tr>
            <td class="col-izq">{{ $gaceta->texto_politico }}</td>
            <td class="col-cen">
                SAN FELIPE,
                {{ ($gaceta->fecha_emision ?? $gaceta->fecha_publicacion) ? mb_strtoupper(($gaceta->fecha_emision ?? $gaceta->fecha_publicacion)->translatedFormat('d \d\e F \d\e Y')) : '__ DE ____________ DE 202_' }}
            </td>
            <td class="col-der">NÚMERO {{ $gaceta->numero }}</td>
        </tr>
    </table>

    <div class="separador-sumario"></div>

    <div class="sumario-titulo">SUMARIO</div>

    @php
        $jerarquiaTipos = [
            'ley' => 1,
            'ley estadal' => 1,
            'decreto' => 2,
            'resolución' => 3,
            'resolucion' => 3,
            'providencia' => 4,
            'providencia administrativa' => 4,
            'acuerdo' => 5,
            'ordenanza' => 6,
            'reglamento' => 7,
            'circular' => 8,
            'auto' => 9,
        ];

        $agrupadoPorInstitucion = $gaceta->solicitudes->groupBy(function ($item) {
            return $item->institucion?->nombre_sumario ?? 'GOBERNACIÓN DEL ESTADO';
        });
    @endphp

    @forelse($agrupadoPorInstitucion as $institucion => $solicitudesInst)
        @php
            $actos = $solicitudesInst->flatMap(function ($solicitud) {
                return $solicitud->actos;
            })->sort(function ($a, $b) use ($jerarquiaTipos) {
                $tipoA = mb_strtolower(trim($a->tipo_acto ?? ''));
                $tipoB = mb_strtolower(trim($b->tipo_acto ?? ''));
                $pesoA = $jerarquiaTipos[$tipoA] ?? 99;
                $pesoB = $jerarquiaTipos[$tipoB] ?? 99;
                if ($pesoA !== $pesoB) {
                    return $pesoA <=> $pesoB;
                }
                return strnatcasecmp($a->numero ?? '', $b->numero ?? '');
            });
        @endphp

        <div class="institucion">{{ $institucion }}</div>

        @forelse($actos as $acto)
            <div class="acto">
                <p class="acto-titulo">
                    {{ $acto->tipo_acto }}
                    {{ !preg_match('/^(n[°ºo\.]|número|numero)/iu', trim((string) $acto->numero)) ? 'N° ' : '' }}{{ $acto->numero }}
                </p>
                <p class="acto-sumario">{{ $acto->sumario }}</p>
            </div>
        @empty
            @foreach($solicitudesInst as $solicitud)
                <div class="acto">{{ mb_strtoupper((string) $solicitud->titulo_acto) }}</div>
            @endforeach
        @endforelse
    @empty
        <p style="text-align: center; color: gray; font-style: italic;">No hay solicitudes registradas en el sumario.</p>
    @endforelse

    <div class="linea-final"></div>
</div>
