<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                <i class="fa-solid fa-file-pdf mr-2 text-emerald-700"></i>
                Digitalización: Gaceta Nro. {{ $gaceta->numero }} / {{ $gaceta->anio }} ({{ $gaceta->tipo }})
            </h2>
            <a href="{{ route('gacetas.digitalizador') }}"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 text-xs font-bold uppercase rounded shadow-sm hover:shadow transition-colors">
                <i class="fa-solid fa-arrow-left mr-2"></i> Cancelar y Volver
            </a>
        </div>
    </x-slot>

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

        $grupos = $gaceta->solicitudes->groupBy(function ($solicitud) {
            return $solicitud->institucion?->nombre_sumario ?? 'GOBERNACIÓN DEL ESTADO';
        });

        // Ordena los actos de cada institución igual que el sumario.
        $grupos = $grupos->map(function ($solicitudesInst) use ($jerarquiaTipos) {
            return $solicitudesInst->flatMap(fn($solicitud) => $solicitud->actos)
                ->sort(function ($a, $b) use ($jerarquiaTipos) {
                    $tipoA = mb_strtolower(trim($a->tipo_acto ?? ''));
                    $tipoB = mb_strtolower(trim($b->tipo_acto ?? ''));
                    $pesoA = $jerarquiaTipos[$tipoA] ?? 99;
                    $pesoB = $jerarquiaTipos[$tipoB] ?? 99;
                    if ($pesoA !== $pesoB) {
                        return $pesoA <=> $pesoB;
                    }
                    return strnatcasecmp($a->numero ?? '', $b->numero ?? '');
                })
                ->values();
        });

        $todosLosActos = $grupos->flatten(1);
        $actosSinPdf = $todosLosActos->filter(fn($acto) => empty($acto->archivo_pdf));
        $actosConPdfCount = $todosLosActos->count() - $actosSinPdf->count();

        // Lista ordenada (institución → acto) para la vista previa con PDF.js.
        $institucionesPorNombre = $gaceta->solicitudes->mapWithKeys(function ($solicitud) {
            return [
                ($solicitud->institucion?->nombre_sumario ?? 'GOBERNACIÓN DEL ESTADO') => $solicitud->institucion,
            ];
        });

        $actosConPdf = collect();
        foreach ($grupos as $institucion => $actos) {
            $institucionModel = $institucionesPorNombre[$institucion] ?? null;
            $tituloLineas = $institucionModel
                ? $institucionModel->tituloActo($gaceta->gobernador)
                : [mb_strtoupper($institucion)];

            foreach ($actos as $acto) {
                if (!empty($acto->archivo_pdf)) {
                    $actosConPdf->push([
                        'url' => asset('storage/' . $acto->archivo_pdf),
                        'titulo' => trim(($acto->tipo_acto ?? 'Acto') . ' ' . ($acto->numero ?? '')),
                        'institucion' => $institucion,
                        'titulo_lineas' => $tituloLineas,
                    ]);
                }
            }
        }
        $actosConPdf = $actosConPdf->values();
    @endphp

    <div class="py-8 bg-gray-100">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-5 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                    <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                    <i class="fa-solid fa-circle-xmark mr-2"></i> {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                    <p class="font-bold mb-2">Por favor corrija los siguientes errores:</p>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex flex-col xl:flex-row gap-6 items-start">

                {{-- ══════════════════════════════════════════════════════
                     COLUMNA IZQUIERDA: VISTA PREVIA LEGAL EN TIEMPO REAL
                     ══════════════════════════════════════════════════════ --}}
                <div class="w-full xl:w-2/3">
                    <div class="mb-3 flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                            <i class="fa-solid fa-eye mr-1.5"></i> Vista Previa en Tiempo Real
                        </span>
                        <span class="text-xs text-gray-500">Los PDFs asignados se muestran en el orden del sumario.</span>
                    </div>

                    <div class="bg-gray-200 py-8 px-4 rounded-lg">
                        <div class="space-y-8 w-full flex flex-col items-center">

                            {{-- Portada + sumario con títulos y firmas por institución --}}
                            @include('gacetas.partials.portada')

                            {{-- Páginas de cada PDF de acto, renderizadas con PDF.js --}}
                            <div id="pdf-pages-container" class="space-y-8 w-full flex flex-col items-center">
                                <div id="pdf-loading" class="text-center text-gray-500 font-bold py-10">
                                    <i class="fa-solid fa-spinner fa-spin text-3xl mb-3"></i><br>
                                    Cargando páginas de los actos...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════
                     COLUMNA DERECHA: CARGA DE PDFs POR ACTO + MAESTRO
                     ══════════════════════════════════════════════════════ --}}
                <div class="w-full xl:w-1/3 space-y-6">

                    {{-- Bloque A: PDF maestro / envío a revisión --}}
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-emerald-700 text-white px-6 py-4">
                            <h3 class="text-lg font-bold"><i class="fa-solid fa-paper-plane mr-2"></i> Enviar a Revisión</h3>
                            <p class="text-emerald-100 text-xs mt-0.5">Gaceta Nro. {{ $gaceta->numero }} / {{ $gaceta->anio }}</p>
                        </div>

                        <div class="p-5 space-y-4">

                            @if($gaceta->estado === 'Devuelta a Digitalizador')
                                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded text-sm">
                                    <p class="font-bold"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Gaceta rechazada</p>
                                    <p class="mt-1"><strong>Motivo:</strong> {{ $gaceta->motivo_devolucion }}</p>
                                </div>
                            @endif

                            <div class="bg-slate-50 border border-slate-200 rounded p-3">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-bold text-slate-700">Actos con PDF</span>
                                    <span class="font-bold {{ $actosSinPdf->isEmpty() && $todosLosActos->isNotEmpty() ? 'text-emerald-700' : 'text-amber-700' }}">
                                        {{ $actosConPdfCount }} / {{ $todosLosActos->count() }}
                                    </span>
                                </div>
                                @if($actosSinPdf->isNotEmpty())
                                    <p class="text-xs text-amber-700 mt-1">
                                        Faltan {{ $actosSinPdf->count() }} acto(s). Puede subir el PDF maestro o asignar cada PDF.
                                    </p>
                                @elseif($todosLosActos->isNotEmpty())
                                    <p class="text-xs text-emerald-700 mt-1">
                                        Todos los actos tienen PDF. Si no sube un PDF maestro, se consolidarán automáticamente.
                                    </p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('gacetas.enviar_revision', $gaceta->id) }}" enctype="multipart/form-data">
                                @csrf

                                <div class="mb-4">
                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                        PDF Maestro (opcional si todos los actos tienen PDF)
                                    </label>
                                    <input type="file" name="archivo_pdf_completo" accept=".pdf"
                                        @if($actosSinPdf->isNotEmpty() || $todosLosActos->isEmpty()) required @endif
                                        class="block w-full text-sm text-gray-500
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-md file:border-0
                                            file:text-sm file:font-bold
                                            file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100
                                            border border-gray-300 rounded-md p-2 transition-colors focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                                    <p class="text-xs text-gray-500 mt-2">
                                        <i class="fa-solid fa-shield-halved mr-1"></i> Formato PDF. Límite: 20MB.
                                    </p>
                                </div>

                                <button type="submit"
                                    onclick="event.preventDefault(); const form = this.closest('form'); if (!form.reportValidity()) return; Swal.fire({ title: '¿Enviar a revisión final?', text: 'Confirme que el escaneo es correcto. La gaceta pasará a la bandeja de la Jefatura.', icon: 'question', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, enviar a revisión', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });"
                                    class="w-full inline-flex justify-center items-center py-3 px-6 border border-transparent shadow-md text-sm font-bold rounded-md text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                                    <i class="fa-solid fa-paper-plane mr-2"></i> Enviar a Jefatura para Revisión
                                </button>
                            </form>

                            <div class="border-t pt-4">
                                <button type="button" onclick="document.getElementById('devolucion-box').classList.toggle('hidden')"
                                    class="w-full inline-flex justify-center items-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-bold rounded-md text-red-600 bg-white hover:bg-red-50 transition-colors">
                                    <i class="fa-solid fa-rotate-left mr-2"></i> Devolver a Jefatura
                                </button>

                                <div id="devolucion-box" class="hidden mt-3 bg-red-50 p-3 rounded-md border border-red-200">
                                    <form action="{{ route('gacetas.devolver', $gaceta->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <label class="block text-xs font-bold text-red-900 mb-1">Motivo de la Devolución *</label>
                                        <textarea name="motivo_devolucion" rows="3" required
                                            class="w-full rounded-md border-red-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                            placeholder="Ej: Faltan firmas, escaneo ilegible..."></textarea>
                                        <div class="mt-2 flex justify-end">
                                            <button type="submit"
                                                onclick="event.preventDefault(); const form = this.closest('form'); if (!form.reportValidity()) return; Swal.fire({ title: '¿Devolver esta Gaceta?', text: 'La gaceta volverá a la Jefatura con el motivo indicado.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, confirmar devolución', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });"
                                                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow-sm text-sm transition">
                                                Confirmar Devolución
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bloque B: PDFs por acto administrativo --}}
                    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-blue-900 text-white px-6 py-4">
                            <h3 class="text-lg font-bold"><i class="fa-solid fa-file-circle-plus mr-2"></i> PDF por Acto Administrativo</h3>
                            <p class="text-blue-200 text-xs mt-0.5">Asigne un archivo a cada acto del sumario.</p>
                        </div>

                        <div class="p-4 space-y-5 max-h-[70vh] overflow-y-auto">
                            @forelse($grupos as $institucion => $actos)
                                <div>
                                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-1 mb-3">
                                        {{ $institucion }}
                                    </h4>

                                    @forelse($actos as $acto)
                                        <div class="mb-3 rounded-md border border-slate-200 bg-slate-50 p-3">
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="font-bold text-sm text-slate-800 leading-tight">
                                                    {{ $acto->tipo_acto }}
                                                    {{ !preg_match('/^(n[°ºo\.]|número|numero)/iu', trim((string) $acto->numero)) ? 'N° ' : '' }}{{ $acto->numero }}
                                                </p>
                                                @if($acto->archivo_pdf)
                                                    <span class="flex-shrink-0 text-[10px] font-bold text-emerald-700 bg-emerald-100 border border-emerald-300 rounded-full px-2 py-0.5">
                                                        <i class="fa-solid fa-circle-check mr-1"></i> PDF
                                                    </span>
                                                @else
                                                    <span class="flex-shrink-0 text-[10px] font-bold text-amber-700 bg-amber-100 border border-amber-300 rounded-full px-2 py-0.5">
                                                        <i class="fa-solid fa-clock mr-1"></i> Pendiente
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="text-xs text-gray-600 mt-1 mb-2">{{ \Illuminate\Support\Str::limit($acto->sumario, 130) }}</p>

                                            <form method="POST" action="{{ route('gacetas.actos.pdf', [$gaceta->id, $acto->id]) }}"
                                                enctype="multipart/form-data" class="flex items-center gap-2">
                                                @csrf
                                                <input type="file" name="archivo_pdf" accept=".pdf" required
                                                    class="block w-full text-[11px] text-gray-500
                                                        file:mr-2 file:py-1.5 file:px-2
                                                        file:rounded file:border-0
                                                        file:text-[11px] file:font-bold
                                                        file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100
                                                        border border-gray-300 rounded p-1">
                                                <button type="submit"
                                                    class="flex-shrink-0 inline-flex items-center px-2.5 py-1.5 bg-blue-700 hover:bg-blue-800 text-white text-[11px] font-bold rounded transition-colors">
                                                    <i class="fa-solid fa-upload mr-1"></i> Asignar
                                                </button>
                                            </form>

                                            @if($acto->archivo_pdf)
                                                <div class="mt-2 flex items-center gap-3 text-xs">
                                                    <a href="{{ asset('storage/' . $acto->archivo_pdf) }}" target="_blank"
                                                        class="inline-flex items-center text-blue-700 hover:text-blue-900 font-bold">
                                                        <i class="fa-solid fa-eye mr-1"></i> Ver PDF
                                                    </a>
                                                    <form method="POST" action="{{ route('gacetas.actos.pdf.delete', [$gaceta->id, $acto->id]) }}"
                                                        onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Quitar el PDF de este acto?', text: 'El archivo se desvinculará del acto administrativo.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, quitar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center text-red-600 hover:text-red-800 font-bold">
                                                            <i class="fa-solid fa-trash-can mr-1"></i> Quitar
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-xs italic text-gray-500 mb-3">Sin actos registrados.</p>
                                    @endforelse
                                </div>
                            @empty
                                <p class="text-sm italic text-gray-500">No hay actos registrados en esta Gaceta.</p>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- PDF.JS: renderiza las páginas de cada acto en el orden del sumario --}}
    <script src="{{ asset('assets/vendor/js/pdf.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const documentos = @json($actosConPdf);
            const container = document.getElementById('pdf-pages-container');
            const loadingMsg = document.getElementById('pdf-loading');

            if (!documentos.length) {
                loadingMsg.innerHTML = '<span class="text-gray-500 font-bold"><i class="fa-solid fa-file-circle-question mr-2"></i>Aún no hay PDFs asignados. Suba el primer archivo para ver la vista previa.</span>';
                return;
            }

            pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('assets/vendor/js/pdf.worker.min.js') }}";

            // Recorta el espacio en blanco superior de cada página escaneada.
            function cropWhitespaceTop(sourceCanvas) {
                const ctx = sourceCanvas.getContext('2d', { willReadFrequently: true });
                const width = sourceCanvas.width;
                const height = sourceCanvas.height;
                const imgData = ctx.getImageData(0, 0, width, height).data;
                let cropY = 0;

                for (let y = 0; y < height; y++) {
                    let pixelesOscuros = 0;
                    for (let x = 0; x < width; x++) {
                        const i = (y * width + x) * 4;
                        if (imgData[i] < 220 && imgData[i + 1] < 220 && imgData[i + 2] < 220 && imgData[i + 3] > 10) {
                            pixelesOscuros++;
                        }
                    }
                    if (pixelesOscuros > 15) {
                        cropY = Math.max(0, y - 30);
                        break;
                    }
                }

                if (cropY === 0) return sourceCanvas;

                const finalCanvas = document.createElement('canvas');
                finalCanvas.className = "w-full";
                finalCanvas.width = width;
                finalCanvas.height = height - cropY;
                finalCanvas.getContext('2d').drawImage(
                    sourceCanvas,
                    0, cropY, width, height - cropY,
                    0, 0, width, height - cropY
                );
                return finalCanvas;
            }

            async function renderizarActos() {
                let numPaginaReal = 2; // La portada HTML es la página 1.

                try {
                    for (const doc of documentos) {
                        const pdf = await pdfjsLib.getDocument(doc.url).promise;

                        for (let i = 1; i <= pdf.numPages; i++) {
                            const page = await pdf.getPage(i);
                            const esPar = (numPaginaReal % 2 === 0);
                            const viewport = page.getViewport({ scale: 2.0 });

                            const tempCanvas = document.createElement('canvas');
                            tempCanvas.height = viewport.height;
                            tempCanvas.width = viewport.width;
                            await page.render({ canvasContext: tempCanvas.getContext('2d'), viewport }).promise;

                            const canvasRecortado = cropWhitespaceTop(tempCanvas);

                            const hojaDiv = document.createElement('div');
                            hojaDiv.className = "bg-white shadow-2xl relative text-black";
                            hojaDiv.style.cssText = "width:100%; max-width:850px; padding:3rem 4rem; min-height:1100px; margin-bottom:2rem;";

                            const headerDiv = document.createElement('div');
                            headerDiv.style.borderBottom = "4px solid black";
                            headerDiv.className = "flex justify-between items-end pb-1 mb-8 font-sans font-bold text-sm uppercase";

                            const textPagina = `<span class="text-[1.1rem]">PAGINA N° ${numPaginaReal}</span>`;
                            const textTitulo = `<span class="tracking-widest text-[1rem]">GACETA OFICIAL DEL ESTADO YARACUY</span>`;

                            const colIzq = document.createElement('div');
                            colIzq.className = "w-1/4 text-left";
                            colIzq.innerHTML = esPar ? textPagina : '';

                            const colCen = document.createElement('div');
                            colCen.className = "w-2/4 text-center";
                            colCen.innerHTML = textTitulo;

                            const colDer = document.createElement('div');
                            colDer.className = "w-1/4 text-right";
                            colDer.innerHTML = !esPar ? textPagina : '';

                            headerDiv.appendChild(colIzq);
                            headerDiv.appendChild(colCen);
                            headerDiv.appendChild(colDer);
                            hojaDiv.appendChild(headerDiv);

                            // Título institucional en la primera hoja de cada acto.
                            if (i === 1 && Array.isArray(doc.titulo_lineas) && doc.titulo_lineas.length) {
                                const tituloDiv = document.createElement('div');
                                tituloDiv.className = "text-center font-sans font-bold text-slate-900";

                                doc.titulo_lineas.forEach(function (linea, indice) {
                                    const p = document.createElement('p');
                                    p.className = indice === 0 ? "text-base tracking-wide" : "text-sm tracking-wide";
                                    p.textContent = linea;
                                    tituloDiv.appendChild(p);
                                });

                                hojaDiv.appendChild(tituloDiv);
                            }

                            hojaDiv.appendChild(canvasRecortado);
                            container.appendChild(hojaDiv);

                            numPaginaReal++;
                        }
                    }

                    loadingMsg.style.display = 'none';
                } catch (error) {
                    console.error("Error cargando los PDFs de los actos:", error);
                    loadingMsg.innerHTML = '<span class="text-red-500 font-bold"><i class="fa-solid fa-circle-xmark mr-2"></i>Error al cargar uno de los PDFs. Verifique el archivo.</span>';
                }
            }

            renderizarActos();
        });
    </script>
</x-app-layout>
