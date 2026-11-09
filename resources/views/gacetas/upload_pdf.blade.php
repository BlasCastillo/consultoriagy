<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Digitalización: Gaceta Nro. {{ $gaceta->numero }} / {{ $gaceta->anio }} ({{ $gaceta->tipo }})
            </h2>
            <x-action-button :href="route('gacetas.digitalizador')"
                action="Cancelar y volver"
                icon="fa-solid fa-arrow-left"
                tone="back"
                align="end" />
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
        $storagePublico = \Illuminate\Support\Facades\Storage::disk('public');
        $actosSinPdf = $todosLosActos->filter(
            fn($acto) => empty($acto->archivo_pdf) || !$storagePublico->exists($acto->archivo_pdf)
        );
        $actosConPdfCount = $todosLosActos->count() - $actosSinPdf->count();

        // Lista ordenada (institución → acto) para la vista previa con PDF.js.
        $institucionesPorNombre = $gaceta->solicitudes->mapWithKeys(function ($solicitud) {
            return [
                ($solicitud->institucion?->nombre_sumario ?? 'GOBERNACIÓN DEL ESTADO') => $solicitud->institucion,
            ];
        });

        $actosConPdf = collect();
        $actosPdfFaltantes = collect();
        foreach ($grupos as $institucion => $actos) {
            $institucionModel = $institucionesPorNombre[$institucion] ?? null;
            $tituloLineas = $institucionModel
                ? $institucionModel->tituloActo($gaceta->gobernador)
                : [mb_strtoupper($institucion)];

            foreach ($actos as $acto) {
                if (empty($acto->archivo_pdf)) {
                    continue;
                }

                $tituloActo = trim(($acto->tipo_acto ?? 'Acto') . ' ' . ($acto->numero ?? ''));

                // El archivo pudo registrarse en BD pero no existir en el disco.
                if (!$storagePublico->exists($acto->archivo_pdf)) {
                    $actosPdfFaltantes->push($tituloActo);
                    continue;
                }

                $actosConPdf->push([
                    'url' => route('gacetas.actos.pdf.ver', [$gaceta->id, $acto->id], false),
                    'titulo' => $tituloActo,
                    'institucion' => $institucion,
                    'titulo_lineas' => $tituloLineas,
                ]);
            }
        }
        $actosConPdf = $actosConPdf->values();
        $actosPdfFaltantes = $actosPdfFaltantes->values();
    @endphp

    <div class="bg-slate-100 py-8">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <p class="mb-2 font-bold">Por favor corrija los siguientes errores:</p>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($actosPdfFaltantes->isNotEmpty())
                <div class="alert alert-error">
                    <p class="mb-1 font-bold">Hay PDFs registrados que no existen en el servidor:</p>
                    <p class="text-sm">
                        {{ $actosPdfFaltantes->implode(', ') }}.
                        Vuelva a subir esos archivos para restaurar la vista previa.
                    </p>
                </div>
            @endif

            <div class="flex flex-col xl:flex-row gap-6 items-start">

                {{-- ══════════════════════════════════════════════════════
                     COLUMNA IZQUIERDA: VISTA PREVIA LEGAL EN TIEMPO REAL
                     ══════════════════════════════════════════════════════ --}}
                <div class="w-full xl:w-2/3">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="badge badge-warning">
                            Vista Previa en Tiempo Real
                        </span>
                        <span class="text-xs text-slate-500">Los PDFs asignados se muestran en el orden del sumario.</span>
                    </div>

                    <div class="rounded-lg bg-slate-200 px-4 py-8">
                        <div class="flex w-full flex-col items-center space-y-8">

                            {{-- Portada + sumario con títulos y firmas por institución --}}
                            @include('gacetas.partials.portada')

                            {{-- Páginas de cada PDF de acto, renderizadas con PDF.js --}}
                            <div id="pdf-pages-container" class="space-y-8 w-full flex flex-col items-center">
                                <div id="pdf-loading" class="bg-slate-50 py-10 text-center font-bold text-slate-500">
                                    <i class="fa-solid fa-spinner fa-spin mb-3 text-3xl"></i><br>
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
                    <div class="card overflow-hidden">
                        <div class="bg-emerald-700 px-6 py-4 text-white">
                            <h3 class="text-lg font-bold">Enviar a Revisión</h3>
                            <p class="mt-0.5 text-xs text-emerald-100">Gaceta Nro. {{ $gaceta->numero }} / {{ $gaceta->anio }}</p>
                        </div>

                        <div class="p-5 space-y-4">

                            @if($gaceta->estado === 'Devuelta a Digitalizador')
                                <div class="rounded border-l-4 border-red-500 bg-red-50 p-3 text-sm text-red-700">
                                    <p class="font-bold">Gaceta rechazada</p>
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
                                        Faltan {{ $actosSinPdf->count() }} acto(s) por cargar. Asigne el PDF de cada acto para poder enviar a revisión.
                                    </p>
                                @elseif($todosLosActos->isNotEmpty())
                                    <p class="text-xs text-emerald-700 mt-1">
                                        Todos los actos tienen PDF. Al enviar a revisión se consolidarán automáticamente.
                                    </p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('gacetas.enviar_revision', $gaceta->id) }}">
                                @csrf

                                <button type="submit"
                                    onclick="event.preventDefault(); const form = this.closest('form'); if (!window.validarFormularioSGCJ(form)) return; Swal.fire({ title: '¿Enviar a revisión final?', text: 'Confirme que el escaneo es correcto. La gaceta pasará a la bandeja de la Jefatura.', icon: 'question', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, enviar a revisión', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });"
                                    class="btn btn-primary w-full">
                                    Enviar a Jefatura para Revisión
                                </button>
                            </form>

                            <div class="border-t border-slate-200 pt-4">
                                <button type="button" onclick="document.getElementById('devolucion-box').classList.toggle('hidden')"
                                    class="btn btn-secondary w-full text-red-600">
                                    Devolver a Jefatura
                                </button>

                                <div id="devolucion-box" class="mt-3 hidden rounded-lg border border-red-200 bg-red-50 p-3">
                                    <form action="{{ route('gacetas.devolver', $gaceta->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <label class="mb-1 block text-xs font-bold text-red-900">Motivo de la Devolución *</label>
                                        <textarea name="motivo_devolucion" rows="3" minlength="5" maxlength="1000" required
                                            class="val-texto-limpio w-full rounded-lg border-red-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500"
                                            placeholder="Ej: Faltan firmas, escaneo ilegible...">{{ old('motivo_devolucion') }}</textarea>
                                        <div class="mt-2 flex justify-end">
                                            <button type="submit"
                                                onclick="event.preventDefault(); const form = this.closest('form'); const motivo = form.querySelector('[name=motivo_devolucion]').value.trim(); if (!window.validarFormularioSGCJ(form) || motivo.length < 5) { if (motivo.length < 5) Swal.fire('Atención', 'El motivo de devolución debe tener al menos 5 caracteres.', 'warning'); return; } Swal.fire({ title: '¿Devolver esta Gaceta?', text: 'La gaceta volverá a la Jefatura con el motivo indicado.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#b91c1c', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, confirmar devolución', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });"
                                                class="btn btn-danger btn-sm">
                                                Confirmar Devolución
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Formulario único para subir varios PDFs de actos a la vez. --}}
                    <form id="bulk-upload-form" method="POST"
                        action="{{ route('gacetas.actos.pdf.bulk', $gaceta->id) }}"
                        enctype="multipart/form-data" class="hidden">
                        @csrf
                    </form>

                    {{-- Bloque B: PDFs por acto administrativo --}}
                    <div class="card overflow-hidden">
                        <div class="bg-blue-900 px-6 py-4 text-white">
                            <h3 class="text-lg font-bold">PDF por Acto Administrativo</h3>
                            <p class="mt-0.5 text-xs text-blue-200">
                                Seleccione uno o varios archivos y súbalos todos de una vez.
                            </p>
                        </div>

                        <div class="p-4 space-y-5 max-h-[70vh] overflow-y-auto">
                            @forelse($grupos as $institucion => $actos)
                                <div>
                                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 border-b border-slate-200 pb-1 mb-3">
                                        {{ $institucion }}
                                    </h4>

                                    @forelse($actos as $acto)
                                        @php
                                            $tienePdf = !empty($acto->archivo_pdf) && $storagePublico->exists($acto->archivo_pdf);
                                        @endphp
                                        <div class="mb-3 rounded-md border border-slate-200 bg-slate-50 p-3">
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="font-bold text-sm text-slate-800 leading-tight">
                                                    {{ $acto->tipo_acto }}
                                                    {{ !preg_match('/^(n[°ºo\.]|número|numero)/iu', trim((string) $acto->numero)) ? 'N° ' : '' }}{{ $acto->numero }}
                                                </p>
                                                @if($tienePdf)
                                                    <span class="badge badge-success flex-shrink-0">
                                                        PDF
                                                    </span>
                                                @elseif($acto->archivo_pdf)
                                                    <span class="badge badge-warning flex-shrink-0">
                                                        Falta archivo
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning flex-shrink-0">
                                                        Pendiente
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="mb-2 mt-1 text-xs text-slate-600">{{ \Illuminate\Support\Str::limit($acto->sumario, 130) }}</p>

                                            <input type="file" name="archivos[{{ $acto->id }}]" accept=".pdf"
                                                form="bulk-upload-form" data-bulk-pdf
                                                class="block w-full rounded border border-slate-300 p-1 text-[11px] text-slate-500
                                                    file:mr-2 file:rounded file:border-0 file:bg-blue-50
                                                    file:px-2 file:py-1.5 file:text-[11px] file:font-bold
                                                    file:text-blue-700 hover:file:bg-blue-100">
                                            <p class="mt-1 text-[11px] text-slate-500">
                                                {{ $tienePdf ? 'Seleccione un archivo solo si desea reemplazar el PDF actual.' : 'Sin PDF asignado. Seleccione el archivo para cargarlo.' }}
                                            </p>

                                            @if($acto->archivo_pdf)
                                                <div class="mt-2 flex items-center gap-2">
                                                    @if($tienePdf)
                                                        <x-action-button :href="route('gacetas.actos.pdf.ver', [$gaceta->id, $acto->id], false)"
                                                            action="Ver PDF del acto"
                                                            icon="fa-solid fa-eye"
                                                            tone="view"
                                                            target="_blank" />
                                                    @endif
                                                    <form method="POST" action="{{ route('gacetas.actos.pdf.delete', [$gaceta->id, $acto->id]) }}"
                                                        onsubmit="event.preventDefault(); const form = this; Swal.fire({ title: '¿Quitar el PDF de este acto?', text: 'El archivo se desvinculará del acto administrativo.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#b91c1c', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, quitar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-action-button type="submit"
                                                            action="Quitar PDF del acto"
                                                            icon="fa-solid fa-trash-can"
                                                            tone="delete" />
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="mb-3 text-xs italic text-slate-500">Sin actos registrados.</p>
                                    @endforelse
                                </div>
                            @empty
                                <p class="text-sm italic text-slate-500">No hay actos registrados en esta Gaceta.</p>
                            @endforelse
                        </div>

                        <div class="border-t border-slate-200 bg-white p-4">
                            <p class="mb-2 text-xs text-slate-500">
                                Seleccione los archivos y súbalos todos de una vez. Los actos sin archivo seleccionado no se modifican.
                            </p>
                            <button type="button" onclick="subirPdfsSeleccionados()" class="btn btn-primary w-full">
                                <i class="fa-solid fa-upload mr-2"></i> Subir todos los PDFs seleccionados
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- PDF.JS: renderiza las páginas de cada acto en el orden del sumario --}}
    <script src="{{ asset('assets/vendor/js/pdf.min.js') }}"></script>
    <script>
        function subirPdfsSeleccionados() {
            const form = document.getElementById('bulk-upload-form');
            const inputs = document.querySelectorAll('input[data-bulk-pdf]');
            const seleccionados = [];

            for (const input of inputs) {
                if (!input.files || input.files.length === 0) {
                    continue;
                }

                const archivo = input.files[0];

                if (archivo.type && archivo.type !== 'application/pdf') {
                    Swal.fire('Archivo no válido', 'Solo se permiten archivos PDF.', 'warning');
                    return;
                }

                seleccionados.push(input);
            }

            if (!seleccionados.length) {
                Swal.fire('Atención', 'Seleccione al menos un archivo PDF para subir.', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Subir los PDFs seleccionados?',
                text: 'Se asignarán ' + seleccionados.length + ' archivo(s) a sus actos administrativos.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1e40af',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, subir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        document.addEventListener("DOMContentLoaded", function () {
            const documentos = @json($actosConPdf);
            const container = document.getElementById('pdf-pages-container');
            const loadingMsg = document.getElementById('pdf-loading');

            if (!documentos.length) {
                loadingMsg.innerHTML = '<span class="font-bold text-slate-500">Aún no hay PDFs asignados. Suba el primer archivo para ver la vista previa.</span>';
                return;
            }

            if (typeof pdfjsLib === 'undefined') {
                loadingMsg.innerHTML = '<span class="font-bold text-red-500">No se pudo cargar el visor de PDF. Recargue la página.</span>';
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
                const fallidos = [];

                for (const doc of documentos) {
                    try {
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
                    } catch (error) {
                        console.error("Error cargando el PDF del acto:", doc.titulo, error);
                        fallidos.push(doc.titulo);
                    }
                }

                if (loadingMsg) {
                    loadingMsg.style.display = 'none';
                }

                if (fallidos.length) {
                    const aviso = document.createElement('div');
                    aviso.className = 'w-full max-w-[850px] rounded-lg border-2 border-dashed border-red-300 bg-red-50 px-6 py-4 text-center text-red-700';

                    const titulo = document.createElement('p');
                    titulo.className = 'font-bold';
                    titulo.textContent = 'No se pudieron cargar ' + fallidos.length + ' PDF(s):';

                    const detalle = document.createElement('p');
                    detalle.className = 'mt-1 text-sm';
                    detalle.textContent = fallidos.join(', ') + '. Verifique que los archivos existan en el servidor o vuelva a subirlos.';

                    aviso.appendChild(titulo);
                    aviso.appendChild(detalle);
                    container.appendChild(aviso);
                }
            }

            renderizarActos();
        });
    </script>
</x-app-layout>
