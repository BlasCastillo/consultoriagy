<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Gaceta Oficial - Sumario (acto único)</title>
    <style>
        @page {
            size: letter portrait;
            margin: 0;
        }

        body {
            font-family: sans-serif;
            margin: 0;
            color: #000;
        }

        .hoja {
            position: relative;
            width: 215.9mm;
            height: 279.4mm;
        }

        .bloque {
            position: absolute;
            left: 15mm;
            width: 185.9mm;
        }

        .text-center {
            text-align: center;
        }

        .text-justify {
            text-align: justify;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .titulo-principal {
            top: 9mm;
            font-size: 34px;
            font-weight: 900;
            line-height: 1;
        }

        .titulo-secundario {
            top: 21mm;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 3px;
        }

        .separador-grueso {
            top: 27mm;
            border-top: 2px solid #000;
        }

        .texto-legal {
            top: 30mm;
            font-size: 8.5px;
            line-height: 1.25;
        }

        .separador {
            top: 52mm;
            border-top: 1.5px solid #000;
        }

        .info {
            top: 54.5mm;
            font-size: 11px;
            font-weight: bold;
        }

        .info td {
            vertical-align: bottom;
        }

        .col-izq {
            text-align: left;
            width: 33%;
        }

        .col-cen {
            text-align: center;
            width: 34%;
        }

        .col-der {
            text-align: right;
            width: 33%;
        }

        .separador-info {
            top: 62mm;
            border-top: 1.5px solid #000;
        }

        .sumario-titulo {
            top: 66mm;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 3px;
        }

        .institucion {
            top: 77mm;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
        }

        .acto {
            top: 84mm;
            font-size: 10px;
            line-height: 1.35;
            text-align: justify;
        }

        .acto-tipo {
            font-weight: bold;
            text-transform: uppercase;
        }

        .linea-negra {
            position: absolute;
            left: 15mm;
            top: 100mm;
            width: 185.9mm;
            border-top: 1.5px solid #000;
        }
    </style>
</head>

<body>
    @php
        $acto = $gaceta->solicitudes
            ->flatMap(fn($solicitud) => $solicitud->actos)
            ->first();
    @endphp

    <div class="hoja">
        <div class="bloque titulo-principal text-center">GACETA OFICIAL</div>
        <div class="bloque titulo-secundario text-center">DEL ESTADO YARACUY</div>
        <div class="bloque separador-grueso"></div>

        <div class="bloque texto-legal text-justify">
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
                <td class="col-izq uppercase">{{ $gaceta->texto_politico }}</td>
                <td class="col-cen uppercase">
                    SAN FELIPE,
                    {{ ($gaceta->fecha_emision ?? $gaceta->fecha_publicacion) ? mb_strtoupper(($gaceta->fecha_emision ?? $gaceta->fecha_publicacion)->translatedFormat('d \d\e F \d\e Y')) : '___ DE ____________ DE 202_' }}
                </td>
                <td class="col-der uppercase">NÚMERO {{ $gaceta->numero }}</td>
            </tr>
        </table>

        <div class="bloque separador-info"></div>

        <div class="bloque sumario-titulo uppercase">SUMARIO</div>

        @if($acto)
            <div class="bloque institucion uppercase">
                {{ $gaceta->solicitudes->first()?->institucion?->nombre_sumario ?? 'GOBERNACIÓN DEL ESTADO' }}
            </div>
            <div class="bloque acto">
                <span class="acto-tipo">
                    {{ $acto->tipo_acto }}
                    {{ !preg_match('/^(n[°ºo\.]|número|numero)/iu', trim((string) $acto->numero)) ? 'N° ' : '' }}{{ $acto->numero }}
                </span>
                <div>{{ $acto->sumario }}</div>
            </div>
        @endif

        <div class="linea-negra"></div>
    </div>
</body>

</html>
