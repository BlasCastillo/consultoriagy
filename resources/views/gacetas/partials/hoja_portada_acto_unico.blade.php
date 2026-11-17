{{-- ── PORTADA + SUMARIO PARA ACTO ÚNICO (fuente única de verdad) ────────
     Igual que hoja_portada.blade.php, pero con posiciones fijas en mm para que
     la línea negra quede siempre en 118mm y el acto continúe en la misma hoja
     desde 122mm, tanto en la Vista Previa Legal como en el PDF con DomPDF.
     ──────────────────────────────────────────────────────────────────── --}}
<style>
    .gacetahoja-unico {
        position: relative;
        width: 215.9mm;
        height: 279.4mm;
        box-sizing: border-box;
        background: #fff;
        color: #000;
        font-family: sans-serif;
    }

    .gacetahoja-unico .bloque {
        position: absolute;
        left: 16.93mm;
        width: 182.04mm;
    }

    .gacetahoja-unico .titulo {
        top: 12.7mm;
        text-align: center;
        font-family: sans-serif;
        font-weight: 900;
        font-size: 80px;
        line-height: 0.85;
        white-space: nowrap;
        margin: 0;
    }

    .gacetahoja-unico .subtitulo {
        top: 32mm;
        text-align: center;
        font-family: serif;
        font-size: 27.2px;
        font-weight: bold;
        letter-spacing: 0.18em;
        white-space: nowrap;
        margin: 0;
    }

    .gacetahoja-unico .separador-grueso {
        top: 41mm;
        border-top: 4px solid #000;
    }

    .gacetahoja-unico .legal {
        top: 44mm;
        font-family: sans-serif;
        font-size: 9.92px;
        line-height: 1.25;
        text-align: justify;
        margin: 0;
    }

    .gacetahoja-unico .separador {
        top: 62mm;
        border-top: 3px solid #000;
    }

    .gacetahoja-unico .info {
        top: 65mm;
        width: 182.04mm;
        font-family: sans-serif;
        font-weight: bold;
        font-size: 15.2px;
        text-transform: uppercase;
        margin: 0;
        border-collapse: collapse;
    }

    .gacetahoja-unico .info td {
        vertical-align: bottom;
        padding: 0;
    }

    .gacetahoja-unico .col-izq { text-align: left; width: 33%; }
    .gacetahoja-unico .col-cen { text-align: center; width: 34%; }
    .gacetahoja-unico .col-der {
        text-align: right;
        width: 33%;
        font-size: 18px;
        letter-spacing: 2px;
    }

    .gacetahoja-unico .separador-sumario {
        top: 76mm;
        border-top: 3px solid #000;
    }

    .gacetahoja-unico .sumario-titulo {
        top: 79mm;
        text-align: center;
        font-family: sans-serif;
        font-weight: bold;
        font-size: 18px;
        letter-spacing: 0.2em;
        margin: 0;
    }

    .gacetahoja-unico .institucion {
        top: 87mm;
        text-align: center;
        font-weight: bold;
        font-size: 16px;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin: 0;
    }

    .gacetahoja-unico .acto {
        top: 94mm;
        font-family: sans-serif;
        font-size: 13.6px;
        line-height: 1.625;
        text-align: justify;
        margin: 0;
    }

    .gacetahoja-unico .acto-titulo {
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0 0 2px 0;
    }

    .gacetahoja-unico .acto-sumario {
        font-weight: normal;
        margin: 0;
    }

    .gacetahoja-unico .linea-negra {
        position: absolute;
        left: 15mm;
        top: 112mm;
        width: 185.9mm;
        border-top: 4px solid #000;
    }

    .gacetahoja-unico #pdf-pages-container {
        position: absolute;
        left: 15mm;
        top: 116mm;
        width: 185.9mm;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
</style>

<div id="hoja-portada" class="gacetahoja-unico shadow-2xl">
    <div class="bloque titulo">GACETA OFICIAL</div>
    <div class="bloque subtitulo">DEL ESTADO YARACUY</div>
    <div class="bloque separador-grueso"></div>

    <div class="bloque legal">
        Los actos de los Poderes Públicos y aquellos cuya inclusión sea conveniente por el Ejecutivo Estadal
        o los demás órganos o Entes de la Administración Pública Estadal, que registre la Gaceta Oficial del
        Estado, tendrán fuerza de documento público desde que aparezcan publicados en ella, artículo 2º del
        Decreto del 10 de septiembre de 1.909 publicado en Gaceta Oficial del Edo. Yaracuy N° 01 del
        11/09/1.909, y en cumplimiento con lo establecido en los Artículos 11, 12, 13 y 14 de la Ley N° 2-
        Ley de Publicaciones Oficiales del Estado Yaracuy, Gaceta Oficial N° 2.565 del 23/01/2003.
        (Impresión en papel Bond por autorización según resolución N° 001 de la secretaria de Despacho, de
        fecha 13 de septiembre de 2013).
    </div>

    <div class="bloque separador"></div>

    <table class="bloque info">
        <tr>
            <td class="col-izq">{{ $gaceta->texto_politico }}</td>
            <td class="col-cen">
                SAN FELIPE,
                {{ ($gaceta->fecha_emision ?? $gaceta->fecha_publicacion) ? mb_strtoupper(($gaceta->fecha_emision ?? $gaceta->fecha_publicacion)->translatedFormat('d \d\e F \d\e Y')) : '__ DE ____________ DE 202_' }}
            </td>
            <td class="col-der">NÚMERO {{ $gaceta->numero }}</td>
        </tr>
    </table>

    <div class="bloque separador-sumario"></div>
    <div class="bloque sumario-titulo">SUMARIO</div>

    @php
        $actoUnicoModelo = $gaceta->solicitudes
            ->flatMap(fn($solicitud) => $solicitud->actos)
            ->first();
    @endphp

    @if($actoUnicoModelo)
        <div class="bloque institucion">
            {{ $gaceta->solicitudes->first()?->institucion?->nombre_sumario ?? 'GOBERNACIÓN DEL ESTADO' }}
        </div>
        <div class="bloque acto">
            <p class="acto-titulo">
                {{ $actoUnicoModelo->tipo_acto }}
                {{ !preg_match('/^(n[°ºo\.]|número|numero)/iu', trim((string) $actoUnicoModelo->numero)) ? 'N° ' : '' }}{{ $actoUnicoModelo->numero }}
            </p>
            <p class="acto-sumario">{{ $actoUnicoModelo->sumario }}</p>
        </div>
    @endif

    <div class="linea-negra"></div>

    <div id="pdf-pages-container">
        @unless($paraPdf ?? false)
            <div id="pdf-loading" style="width:100%; padding:24px 0; text-align:center; font-weight:bold; color:#64748b;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size:2rem; margin-bottom:12px;"></i><br>
                Cargando el acto...
            </div>
        @endunless
    </div>
</div>
