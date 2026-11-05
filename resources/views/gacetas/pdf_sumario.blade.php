<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Gaceta Oficial - Sumario</title>
    <style>
        @page {
            size: letter portrait;
            margin: 40px 50px 50px 50px;
        }

        body {
            font-family: sans-serif;
        }

        .text-center {
            text-align: center;
        }

        .text-justify {
            text-align: justify;
        }

        .font-bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .tracking-widest {
            letter-spacing: 2px;
        }

        .mb-2 {
            margin-bottom: 0.5rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .mb-10 {
            margin-bottom: 1.5rem;
        }

        h1 {
            font-size: 3.5rem;
            line-height: 1;
            margin: 0;
            padding: 0;
            transform: scaleY(0.85);
        }

        h2 {
            font-size: 1.2rem;
            margin-top: 5px;
        }

        .separator {
            border-top: 2px solid black;
            width: 100%;
            margin: 6px 0;
        }

        .separator-thick {
            border-top: 3px solid black;
            width: 100%;
            margin: 10px 0;
        }

        .header-info {
            width: 100%;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: bold;
        }

        .header-info td {
            vertical-align: bottom;
        }

        .col-left {
            text-align: left;
            width: 33%;
        }

        .col-center {
            text-align: center;
            width: 34%;
        }

        .col-right {
            text-align: right;
            width: 33%;
        }

        .small-text {
            font-size: 9px;
            line-height: 1.2;
        }

        .sumario-title {
            font-size: 16px;
            letter-spacing: 3px;
            margin-bottom: 16px;
            font-weight: bold;
        }

        .institucion-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
            padding-bottom: 3px;
        }

        .acto-item {
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 10px;
            text-align: justify;
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    <div class="text-center">
        <h1>GACETA OFICIAL</h1>
        <h2 class="tracking-widest font-bold">DEL ESTADO YARACUY</h2>
    </div>

    <div class="separator-thick"></div>

    <div class="small-text text-justify mb-2">
        Los actos de los Poderes Públicos y aquellos cuya inclusión sea conveniente por el Ejecutivo Estadal
        o los demás órganos o Entes de la Administración Pública Estadal, que registre la Gaceta Oficial del
        Estado, tendrán fuerza de documento público desde que aparezcan publicados en ella, artículo 2º del
        Decreto del 10 de septiembre de 1.909 publicado en Gaceta Oficial del Edo. Yaracuy N° 01 del
        11/09/1.909, y en cumplimiento con lo establecido en los Artículos 11, 12, 13 y 14 de la Ley N° 2-
        Ley de Publicaciones Oficiales del Estado Yaracuy, Gaceta Oficial N° 2.565 del 23/01/2003.
        (Impresión en papel Bond por autorización según resolución N° 001 de la secretaria de Despacho, de
        fecha 13 de septiembre de 2013).
    </div>

    <div class="separator"></div>

    <table class="header-info">
        <tr>
            <td class="col-left uppercase">
                <span>
                    {{ $gaceta->texto_politico }}
                </span>
            </td>
            <td class="col-center uppercase">
                SAN FELIPE,
                {{ ($gaceta->fecha_emision ?? $gaceta->fecha_publicacion) ? mb_strtoupper(($gaceta->fecha_emision ?? $gaceta->fecha_publicacion)->translatedFormat('d \d\e F \d\e Y')) : '___ DE ____________ DE 202_' }}
            </td>
            <td class="col-right uppercase">
                NÚMERO {{ $gaceta->numero }}
            </td>
        </tr>
    </table>

    <div class="separator"></div>

    <div class="text-center mb-10">
        <div class="sumario-title uppercase">SUMARIO</div>

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

        <div>
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

                <div class="institucion-title uppercase">{{ $institucion }}</div>

                @forelse($actos as $acto)
                    <div class="acto-item">
                        <span class="font-bold uppercase">
                            {{ $acto->tipo_acto }}
                            {{ !preg_match('/^(n[°ºo\.]|número|numero)/iu', trim((string) $acto->numero)) ? 'N° ' : '' }}{{ $acto->numero }}
                        </span>
                        <div style="font-weight: normal; margin-top: 2px;">
                            {{ $acto->sumario }}
                        </div>
                    </div>
                @empty
                    @foreach($solicitudesInst as $solicitud)
                        <div class="acto-item">
                            {{ mb_strtoupper((string) $solicitud->titulo_acto) }}
                        </div>
                    @endforeach
                @endforelse
            @empty
                <p style="text-align: center; color: gray; font-style: italic;">No hay solicitudes registradas en el
                    sumario.</p>
            @endforelse
        </div>
    </div>
</body>

</html>