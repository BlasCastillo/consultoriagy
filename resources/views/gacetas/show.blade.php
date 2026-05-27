<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Vista Previa Legal: Gaceta {{ $gaceta->numero }} / {{ $gaceta->anio }}
            </h2>
            <div class="flex space-x-3">
                @if($gaceta->estado === 'Publicada' && $gaceta->ruta_archivo)
                    <a href="{{ asset('gacetas_pdf/' . $gaceta->ruta_archivo) }}" target="_blank" download
                        class="inline-flex items-center px-4 py-2 bg-blue-900 hover:bg-blue-800 text-white font-bold text-xs uppercase rounded transition-colors shadow-md">
                        <i class="fa-solid fa-download mr-2"></i> Descargar PDF Oficial
                    </a>
                @endif
                <a href="{{ route('gacetas.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 font-bold text-xs uppercase rounded transition-colors shadow-sm">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Volver al listado
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-200">
        <div class="space-y-12 w-full flex flex-col items-center">

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
                        <span style="border-bottom: 2px solid black; padding-bottom: 1px;">
                            {{ $gaceta->anio_politico ?? 'AÑO CXIV' }} – {{ $gaceta->mes_politico ?? 'MES IV' }}
                        </span>
                    </div>
                    <div class="w-1/3 text-center text-[0.95rem]">
                        SAN FELIPE,
                        {{ $gaceta->fecha_emision ? mb_strtoupper($gaceta->fecha_emision->translatedFormat('d \d\e F \d\e Y')) : '___ DE ____________ DE 202_' }}
                    </div>
                    <div class="w-1/3 text-right text-lg tracking-wide">
                        NÚMERO {{ $gaceta->numero }}
                    </div>
                </div>

                <div style="border-top: 3px solid black; width: 100%; margin-top: 4px; margin-bottom: 16px;"></div>

                <div class="text-center font-sans font-bold mb-10">
                    <h3 class="text-lg tracking-[0.2em] mb-4">SUMARIO</h3>

                    @php
                        $agrupadoPorInstitucion = $gaceta->sumarios->groupBy(function ($item) {
                            return $item->institucion->name ?? 'GOBERNACIÓN DEL ESTADO';
                        });
                    @endphp

                    <div class="space-y-10 text-left px-2">
                        @forelse($agrupadoPorInstitucion as $institucion => $sumariosInst)
                            <div>
                                <h4 class="text-center text-md uppercase font-bold mb-6 tracking-wide">{{ $institucion }}
                                </h4>
                                <div class="space-y-5 font-sans text-[0.85rem] leading-relaxed text-justify">
                                    @foreach($sumariosInst as $sumario)
                                        <p>
                                            <strong>{{ mb_strtoupper($sumario->tipo_acto) }} N°
                                                {{ $sumario->numero_acto }}:</strong> {{ $sumario->descripcion }}
                                        </p>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-center italic text-gray-500">No hay actos registrados en el sumario.</p>
                        @endforelse
                    </div>
                </div>

                <div style="border-top: 4px solid black; width: 100%; margin-top: 24px; margin-bottom: 8px;"></div>

                <div id="firma-gobernador" class="text-center font-sans font-bold text-[0.95rem] leading-tight pb-2">
                    <p>{{ mb_strtoupper($gaceta->gobernador->titulo->abreviatura ?? 'LCDO.') }}
                        {{ mb_strtoupper($gaceta->gobernador->nombres ?? 'JULIO CÉSAR') }}
                        {{ mb_strtoupper($gaceta->gobernador->apellidos ?? 'LEÓN HEREDIA') }}</p>
                    <p>GOBERNADOR DEL ESTADO YARACUY</p>
                </div>

            </div> @if($gaceta->estado === 'Publicada' && $gaceta->ruta_archivo)
                <div id="pdf-pages-container" class="space-y-12 w-full flex flex-col items-center">
                    <div id="pdf-loading" class="text-center text-gray-500 font-bold py-10">
                        <i class="fa-solid fa-spinner fa-spin text-3xl mb-3"></i><br>
                        Calculando espacios y estructurando el documento legal...
                    </div>
                </div>
            @else
                <div class="bg-white shadow-2xl mx-auto relative text-black"
                    style="width: 100%; max-width: 850px; padding: 3rem 4rem;">
                    <div class="w-full py-16 bg-gray-50 border-2 border-dashed border-gray-300 text-center text-gray-500">
                        <i class="fa-solid fa-file-pdf text-4xl mb-3 text-gray-300"></i>
                        <p>Las páginas del documento aparecerán aquí cuando el PDF sea subido.</p>
                    </div>
                </div>
            @endif

        </div>
    </div>

    @if($gaceta->estado === 'Publicada' && $gaceta->ruta_archivo)
        <script src="{{ asset('assets/vendor/js/pdf.min.js') }}"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const url = "{{ asset('gacetas_pdf/' . $gaceta->ruta_archivo) }}";
                pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('assets/vendor/js/pdf.worker.min.js') }}";

                // ==========================================
                // FUNCIÓN MÁGICA: ESCÁNER DE PÍXELES PARA RECORTAR ESPACIOS EN BLANCO
                // ==========================================
                function cropWhitespaceTop(sourceCanvas) {
                    const ctx = sourceCanvas.getContext('2d', { willReadFrequently: true });
                    const width = sourceCanvas.width;
                    const height = sourceCanvas.height;
                    const imgData = ctx.getImageData(0, 0, width, height).data;

                    let cropY = 0;

                    // Escaneamos la imagen de arriba hacia abajo, fila por fila
                    for (let y = 0; y < height; y++) {
                        let pixelesOscuros = 0;
                        for (let x = 0; x < width; x++) {
                            const i = (y * width + x) * 4;
                            // Si el pixel no es blanco puro (ej. texto negro/gris)
                            if (imgData[i] < 220 && imgData[i + 1] < 220 && imgData[i + 2] < 220 && imgData[i + 3] > 10) {
                                pixelesOscuros++;
                            }
                        }
                        // Si encontramos una fila con más de 15 píxeles oscuros, ¡Ahí empieza el texto!
                        if (pixelesOscuros > 15) {
                            // Guardamos esa posición Y, dejándole un pequeño margen de "respiro" de 30px arriba
                            cropY = Math.max(0, y - 30);
                            break;
                        }
                    }

                    if (cropY === 0) return sourceCanvas; // Si no hay espacios, devuelve igual

                    // Creamos un nuevo canvas solo con la parte útil
                    const finalCanvas = document.createElement('canvas');
                    finalCanvas.className = "w-full";
                    finalCanvas.width = width;
                    finalCanvas.height = height - cropY;
                    const finalCtx = finalCanvas.getContext('2d');

                    finalCtx.drawImage(
                        sourceCanvas,
                        0, cropY, width, height - cropY, // Origen (Cortado)
                        0, 0, width, height - cropY      // Destino
                    );

                    return finalCanvas;
                }

                async function renderizarGaceta() {
                    const container = document.getElementById('pdf-pages-container');
                    const portadaHtml = document.getElementById('hoja-portada');
                    const loadingMsg = document.getElementById('pdf-loading');

                    try {
                        const pdf = await pdfjsLib.getDocument(url).promise;
                        loadingMsg.style.display = 'none';

                        for (let i = 1; i <= pdf.numPages; i++) {
                            const page = await pdf.getPage(i);
                            const numPaginaReal = i + 1; // Portada es 1, PDF empieza en 2
                            const esPar = (numPaginaReal % 2 === 0);

                            // Renderizamos la página en un canvas temporal oculto
                            const viewport = page.getViewport({ scale: 2.0 });
                            const tempCanvas = document.createElement('canvas');
                            tempCanvas.height = viewport.height;
                            tempCanvas.width = viewport.width;
                            const tempCtx = tempCanvas.getContext('2d');

                            await page.render({ canvasContext: tempCtx, viewport: viewport }).promise;

                            // Aplicamos la Inteligencia de Recorte
                            const canvasRecortado = cropWhitespaceTop(tempCanvas);

                            // LÓGICA DE DISTRIBUCIÓN
                            if (i === 1) {
                                // LA HOJA 1 DEL PDF SE PEGA DENTRO DE LA PORTADA
                                // Así llena el espacio vacío debajo del Gobernador
                                portadaHtml.style.marginBottom = "0";
                                portadaHtml.appendChild(canvasRecortado);
                            } else {
                                // LAS HOJAS 2 EN ADELANTE CREAN NUEVAS PÁGINAS
                                const hojaDiv = document.createElement('div');
                                hojaDiv.className = "bg-white shadow-2xl relative text-black";
                                hojaDiv.style.width = "100%";
                                hojaDiv.style.maxWidth = "850px";
                                hojaDiv.style.padding = "3rem 4rem";
                                hojaDiv.style.minHeight = "1100px";
                                // Mantenemos el margen inferior para que se vean separadas
                                hojaDiv.style.marginBottom = "3rem";

                                // ENCABEZADO CENTRADO EXACTO CON 3 COLUMNAS
                                const headerDiv = document.createElement('div');
                                headerDiv.style.borderBottom = "4px solid black";
                                headerDiv.className = "flex justify-between items-end pb-1 mb-8 font-sans font-bold text-sm uppercase";

                                const textPagina = `<span class="text-[1.1rem]">PAGINA N° ${numPaginaReal}</span>`;
                                const textTitulo = `<span class="tracking-widest text-[1rem]">GACETA OFICIAL DEL ESTADO YARACUY</span>`;

                                // Columna Izquierda (25%)
                                const colIzq = document.createElement('div');
                                colIzq.className = "w-1/4 text-left";
                                colIzq.innerHTML = esPar ? textPagina : '';

                                // Columna Central (50%)
                                const colCen = document.createElement('div');
                                colCen.className = "w-2/4 text-center";
                                colCen.innerHTML = textTitulo;

                                // Columna Derecha (25%)
                                const colDer = document.createElement('div');
                                colDer.className = "w-1/4 text-right";
                                colDer.innerHTML = !esPar ? textPagina : '';

                                headerDiv.appendChild(colIzq);
                                headerDiv.appendChild(colCen);
                                headerDiv.appendChild(colDer);

                                hojaDiv.appendChild(headerDiv);
                                hojaDiv.appendChild(canvasRecortado); // Pegamos el PDF recortado
                                container.appendChild(hojaDiv);
                            }
                        }
                    } catch (error) {
                        console.error("Error cargando el PDF: ", error);
                        loadingMsg.innerHTML = '<span class="text-red-500 font-bold">Error al analizar el documento. Verifique el archivo.</span>';
                    }
                }

                renderizarGaceta();
            });
        </script>
    @endif
</x-app-layout>