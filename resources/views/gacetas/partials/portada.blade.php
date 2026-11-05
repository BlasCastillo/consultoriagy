{{-- ── HOJA PORTADA + SUMARIO (reutilizable) ─────────────────────────────
     Requiere: $gaceta con relaciones solicitudes.institucion y
     solicitudes.actos cargadas.
     ──────────────────────────────────────────────────────────────────── --}}
<div id="hoja-portada" class="bg-white shadow-2xl relative overflow-hidden text-black pb-12"
    style="width: 100%; max-width: 850px; padding-top: 3rem; padding-left: 4rem; padding-right: 4rem; min-height: 1100px;">

    <div class="text-center">
        <h1 class="font-sans font-black tracking-tighter"
            style="font-size: 5rem; line-height: 1; transform: scaleY(0.85); margin-bottom: -10px;">
            GACETA OFICIAL
        </h1>
    </div>

    <div class="text-center mb-1">
        <h2 class="font-serif text-[1.7rem] font-bold tracking-widest" style="letter-spacing: 0.18em;">
            DEL ESTADO YARACUY
        </h2>
    </div>

    <div style="border-top: 4px solid black; width: 100%; margin-top: 4px; margin-bottom: 4px;"></div>

    <div class="font-sans text-[0.62rem] leading-tight text-justify mb-1">
        Los actos de los Poderes Públicos y aquellos cuya inclusión sea conveniente por el Ejecutivo Estadal
        o los demás órganos o Entes de la Administración Pública Estadal, que registre la Gaceta Oficial del
        Estado, tendrán fuerza de documento público desde que aparezcan publicados en ella, artículo 2º del
        Decreto del 10 de septiembre de 1.909 publicado en Gaceta Oficial del Edo. Yaracuy N° 01 del
        11/09/1.909, y en cumplimiento con lo establecido en los Artículos 11, 12, 13 y 14 de la Ley N° 2-
        Ley de Publicaciones Oficiales del Estado Yaracuy, Gaceta Oficial N° 2.565 del 23/01/2003.
        (Impresión en papel Bond por autorización según resolución N° 001 de la secretaria de Despacho, de
        fecha 13 de septiembre de 2013).
    </div>

    <div style="border-top: 3px solid black; width: 100%; margin-top: 4px; margin-bottom: 4px;"></div>

    <div class="flex justify-between items-center font-sans font-bold text-sm uppercase">
        <div class="w-1/3 text-left">
            <span style="padding-bottom: 1px;">
                {{ $gaceta->texto_politico }}
            </span>
        </div>
        <div class="w-1/2 text-center text-[0.95rem]">
            SAN FELIPE,
            {{ ($gaceta->fecha_emision ?? $gaceta->fecha_publicacion) ? mb_strtoupper(($gaceta->fecha_emision ?? $gaceta->fecha_publicacion)->translatedFormat('d \d\e F \d\e Y')) : '__ DE ____________ DE 202_' }}
        </div>
        <div class="w-1/3 text-right text-lg tracking-wide">
            NÚMERO {{ $gaceta->numero }}
        </div>
    </div>

    <div style="border-top: 3px solid black; width: 100%; margin-top: 4px; margin-bottom: 16px;"></div>

    <div class="text-center font-sans font-bold mb-10">
        <h3 class="text-lg tracking-[0.2em] mb-4">SUMARIO</h3>

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

            // Agrupamos por nombre de sumario (sin el "Yaracuy" final) conservando
            // el orden de aparición de las solicitudes (orden_sumario).
            $agrupadoPorInstitucion = $gaceta->solicitudes->groupBy(function ($item) {
                return $item->institucion?->nombre_sumario ?? 'GOBERNACIÓN DEL ESTADO';
            });
        @endphp

        <div class="space-y-8 text-left px-2">
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

                <div>
                    {{-- Título de la institución (sin el "Yaracuy" final) --}}
                    <h4 class="text-center text-md uppercase font-bold mb-4 tracking-wide">
                        {{ $institucion }}
                    </h4>

                    <div class="space-y-4 font-sans text-[0.85rem] leading-relaxed text-justify">
                        @forelse($actos as $acto)
                            <div>
                                <p class="font-bold uppercase tracking-wide">
                                    {{ $acto->tipo_acto }}
                                    {{ !preg_match('/^(n[°ºo\.]|número|numero)/iu', trim((string) $acto->numero)) ? 'N° ' : '' }}{{ $acto->numero }}
                                </p>
                                <p class="font-normal text-justify text-gray-900 mt-0.5 leading-relaxed">
                                    {{ $acto->sumario }}
                                </p>
                            </div>
                        @empty
                            @foreach($solicitudesInst as $solicitud)
                                <p class="font-normal text-justify text-gray-900">
                                    {{ mb_strtoupper((string) $solicitud->titulo_acto) }}
                                </p>
                            @endforeach
                        @endforelse
                    </div>
                </div>
            @empty
                <p class="text-center italic text-gray-500 font-normal">No hay solicitudes registradas en el sumario.</p>
            @endforelse
        </div>
    </div>

    <div style="border-top: 4px solid black; width: 100%; margin-top: 24px; margin-bottom: 8px;"></div>
</div>{{-- /hoja-portada --}}
