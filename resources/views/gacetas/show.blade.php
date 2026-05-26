<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Detalle de Gaceta {{ $gaceta->numero }} / {{ $gaceta->anio }}
            </h2>
            <div class="flex space-x-3">
                @if($gaceta->estado === 'Publicada' && $gaceta->ruta_archivo)
                    <a href="{{ asset('storage/' . $gaceta->ruta_archivo) }}" target="_blank" download class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold text-xs uppercase rounded transition shadow">
                        <i class="fa-solid fa-download mr-2"></i> Descargar PDF Oficial
                    </a>
                @endif
                <a href="{{ route('gacetas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold text-xs uppercase rounded transition">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Volver al listado
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Alertas -->
            @if($gaceta->dias_retraso > 0)
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-circle-exclamation text-red-500 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">Retraso de <strong>{{ $gaceta->dias_retraso }} días hábiles</strong> desde su recepción en despacho.</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($gaceta->corregida_de_id && $gaceta->corregidaDe)
                <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-md shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-circle-info text-purple-500 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-purple-700">Aviso: Imprimida por error de copia y corrige a la <a href="{{ route('gacetas.show', $gaceta->corregidaDe->id) }}" class="font-bold underline">Gaceta Nro. {{ $gaceta->corregidaDe->numero }}</a>.</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($gaceta->correcciones->count() > 0)
                <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-md shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-orange-500 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-orange-700">Aviso: Corregida por error de copia en ediciones posteriores.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 1) Encabezado estructurado con los datos & 2) Sumario detallado -->
            <div class="bg-white p-8 md:p-14 shadow-2xl font-serif text-gray-900 border border-gray-300 relative" style="max-width: 800px; margin: 0 auto; min-height: 900px; padding-bottom: 80px;">
                
                <!-- Cintillos Legales -->
                <div class="text-[0.65rem] md:text-xs text-center border-b border-gray-400 pb-2 mb-6 uppercase tracking-wider leading-tight">
                    <p>Artículo 5 de la Ley de Publicaciones Oficiales (2003)</p>
                    <p>Decreto Nro. 24 del 23 de Enero de 1909</p>
                </div>

                <!-- Encabezado de la Gaceta -->
                <div class="text-center border-b-[5px] border-double border-gray-900 pb-6 mb-8">
                    <img src="{{ asset('img/logo-gobernacion.svg') }}" alt="Escudo Yaracuy" class="h-28 mx-auto mb-4 grayscale" style="filter: grayscale(100%) contrast(120%);">
                    
                    <h1 class="text-4xl font-bold uppercase mb-3" style="font-family: 'Times New Roman', Times, serif; letter-spacing: 0.1em; line-height: 1.1;">
                        GACETA OFICIAL<br>
                        <span class="text-2xl">DEL ESTADO YARACUY</span>
                    </h1>
                    
                    <div class="flex justify-between items-center text-sm font-bold uppercase mt-8 tracking-wider px-2 flex-wrap gap-2">
                        <div>{{ $gaceta->anio_politico ?? 'AÑO CXIV' }} - {{ $gaceta->mes_politico ?? 'MES I' }}</div>
                        <div class="text-base">San Felipe, {{ $gaceta->fecha_emision ? $gaceta->fecha_emision->translatedFormat('d \d\e F \d\e Y') : '____ de ______________ de ____' }}</div>
                        <div>NÚMERO: {{ $gaceta->numero }}</div>
                    </div>

                    @if($gaceta->tipo === 'Extraordinaria')
                        <div class="mt-6 text-xl font-bold uppercase tracking-widest border-2 border-gray-900 inline-block px-6 py-1">Extraordinaria</div>
                    @endif
                </div>

                <!-- Título Sumario -->
                <div class="text-center mb-10">
                    <h2 class="text-2xl font-bold uppercase tracking-widest inline-block border-b-2 border-gray-900 px-12 pb-1">Sumario</h2>
                </div>

                <!-- Contenido Agrupado (Sumario) -->
                <div class="space-y-10 px-4">
                    @php
                        // Agrupar por Institución
                        $agrupadoPorInstitucion = $gaceta->sumarios->groupBy(function($item) {
                            return $item->institucion->name ?? 'INSTITUCIÓN DESCONOCIDA';
                        });
                    @endphp

                    @forelse($agrupadoPorInstitucion as $institucion => $sumariosInst)
                        <div>
                            <h3 class="text-lg font-bold text-center uppercase tracking-wider mb-6 pb-2 border-b border-gray-300">{{ mb_strtoupper($institucion) }}</h3>
                            
                            @php
                                // Agrupar por Tipo de Acto dentro de la institución
                                $agrupadoPorTipo = $sumariosInst->groupBy('tipo_acto');
                            @endphp

                            @foreach($agrupadoPorTipo as $tipoActo => $sumariosTipo)
                                <div class="mb-6 last:mb-0">
                                    <h4 class="text-md font-bold uppercase italic mb-3">{{ mb_strtoupper($tipoActo) }}</h4>
                                    <ul class="space-y-5 pl-2 text-justify leading-relaxed">
                                        @foreach($sumariosTipo as $sumario)
                                            <li class="flex gap-2">
                                                <span class="font-bold whitespace-nowrap">{{ $sumario->numero_acto }}.</span>
                                                <span>{{ $sumario->descripcion }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="text-center italic text-gray-500 my-12">No hay actos registrados en el sumario de esta gaceta.</div>
                    @endforelse
                </div>

                <!-- Pie de página emulado -->
                <div class="absolute bottom-4 left-0 right-0 text-center text-[0.65rem] text-gray-500 uppercase tracking-widest">
                    Imprenta Oficial del Estado Yaracuy - Documento Generado por SGCJ
                </div>
            </div>

            <!-- 3) Visor PDF (Iframe responsivo) al final, acompañado de un botón de descarga -->
            @if($gaceta->estado === 'Publicada' && $gaceta->ruta_archivo)
                <div class="bg-slate-800 p-6 rounded-lg shadow-lg border border-slate-700 space-y-6 max-w-[800px] mx-auto">
                    <div class="flex justify-between items-center text-white px-2 flex-wrap gap-4 border-b border-slate-700 pb-4">
                        <span class="text-sm font-bold flex items-center">
                            <i class="fa-solid fa-file-pdf text-red-500 mr-2 text-lg"></i>
                            Documento Original Escaneado
                        </span>
                        <a href="{{ asset('storage/' . $gaceta->ruta_archivo) }}" target="_blank" download="{{ $gaceta->numero }}_{{ $gaceta->anio }}.pdf" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold uppercase rounded transition flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Descargar Archivo PDF
                        </a>
                    </div>
                    
                    <div class="p-8 text-center text-slate-300">
                        <p class="mb-4">Para visualizar el documento completo, utilice el botón de descarga superior.</p>
                        <p class="text-xs text-slate-500">Nota: El bloqueo (Error 403) es una medida de seguridad local del servidor XAMPP para prevenir ejecución de scripts embebidos.</p>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
