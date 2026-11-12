<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                    Recortar PDF del acto administrativo
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Gaceta Nro. {{ $gaceta->numero }} / {{ $gaceta->anio }} ·
                    {{ $acto->tipo_acto }} {{ $acto->numero }}
                </p>
            </div>
            <x-action-button :href="route('gacetas.upload_pdf', $gaceta->id)"
                action="Volver a digitalización"
                icon="fa-solid fa-arrow-left"
                tone="back"
                align="end" />
        </div>
    </x-slot>

    <div class="bg-slate-100 py-8">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">

            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
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

            <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                <p class="font-bold"><i class="fa-solid fa-scissors mr-1"></i> Cómo recortar este acto</p>
                <ul class="mt-2 list-disc list-inside space-y-1">
                    <li>Desmarque <strong>Conservar</strong> en las hojas que no deben aparecer en el documento.</li>
                    <li>Pulse <strong>Recortar zona</strong> y arrastre sobre una hoja para delimitar el área que sí desea conservar.</li>
                    <li>Al guardar, el PDF se reconstruye sin las hojas excluidas. El original queda respaldado en el servidor.</li>
                </ul>
            </div>

            <div class="flex flex-col xl:flex-row gap-6 items-start">

                {{-- Hojas del PDF --}}
                <div class="w-full xl:w-2/3">
                    <div id="visor" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div id="cargando-pdf" class="col-span-full rounded-lg bg-white p-10 text-center font-bold text-slate-500 shadow">
                            <i class="fa-solid fa-spinner fa-spin mb-3 text-3xl"></i><br>
                            Cargando las hojas del PDF...
                        </div>
                    </div>
                </div>

                {{-- Panel de acciones --}}
                <aside class="w-full xl:w-1/3 space-y-5 xl:sticky xl:top-6">
                    <div class="card overflow-hidden">
                        <div class="bg-blue-900 px-6 py-4 text-white">
                            <h3 class="text-lg font-bold">Resumen del recorte</h3>
                            <p class="mt-0.5 text-xs text-blue-200">{{ $acto->tipo_acto }} {{ $acto->numero }}</p>
                        </div>

                        <div class="space-y-4 p-5">
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">Archivo actual</dt>
                                    <dd class="text-right font-semibold text-slate-800">{{ basename($acto->archivo_pdf) }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">Peso actual</dt>
                                    <dd class="font-semibold text-slate-800">{{ number_format($tamanoMb, 2, ',', '.') }} MB</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">Hojas totales</dt>
                                    <dd id="total-hojas" class="font-semibold text-slate-800">—</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">Hojas a conservar</dt>
                                    <dd id="hojas-conservadas" class="font-bold text-emerald-700">—</dd>
                                </div>
                            </dl>

                            <div class="flex flex-wrap gap-2 border-t border-slate-200 pt-3">
                                <button type="button" id="btn-conservar-todas"
                                    class="rounded border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Conservar todas
                                </button>
                                <button type="button" id="btn-excluir-todas"
                                    class="rounded border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Excluir todas
                                </button>
                            </div>

                            <form id="form-recorte" method="POST"
                                action="{{ route('gacetas.actos.recortar.save', [$gaceta->id, $acto->id]) }}">
                                @csrf
                                <input type="hidden" name="paginas" id="paginas-json" value="">

                                <button type="submit" id="btn-guardar-recorte" class="btn btn-primary w-full">
                                    <i class="fa-solid fa-scissors mr-2"></i> Guardar recorte del acto
                                </button>
                            </form>

                            <p class="text-xs text-slate-500">
                                El PDF original se respalda automáticamente antes de aplicar los cambios.
                                El consolidado de la gaceta se regenerará en la próxima vista previa.
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    {{-- Plantilla de una hoja --}}
    <template id="plantilla-pagina">
        <div class="pagina-card overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm transition">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 bg-slate-50 px-3 py-2">
                <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                    <input type="checkbox" class="pagina-incluir h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" checked>
                    Conservar hoja <span class="pagina-num text-slate-500"></span>
                </label>
                <div class="flex items-center gap-1">
                    <button type="button"
                        class="btn-recortar rounded border border-emerald-300 bg-emerald-50 px-2 py-1 text-[11px] font-bold text-emerald-700 hover:bg-emerald-100">
                        <i class="fa-solid fa-crop-simple mr-1"></i> Recortar zona
                    </button>
                    <button type="button"
                        class="btn-quitar-recorte hidden rounded border border-slate-300 bg-white px-2 py-1 text-[11px] font-bold text-slate-600 hover:bg-slate-100">
                        Quitar recorte
                    </button>
                </div>
            </div>

            <div class="pagina-lienzo relative select-none bg-slate-200">
                <canvas class="block h-auto w-full"></canvas>

                <div class="crop-capa absolute inset-0 hidden overflow-hidden touch-none">
                    <div class="crop-seleccion absolute hidden border-2 border-emerald-500 bg-emerald-400/20"
                        style="box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.45);"></div>
                </div>

                <div class="pagina-excluida absolute inset-0 hidden items-center justify-center bg-slate-900/70 text-sm font-bold uppercase tracking-wider text-white">
                    <i class="fa-solid fa-eye-slash mr-2"></i> Hoja excluida
                </div>
            </div>
        </div>
    </template>

    <script src="{{ asset('assets/vendor/js/pdf.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            const visor = document.getElementById('visor');
            const plantilla = document.getElementById('plantilla-pagina');
            const totalTexto = document.getElementById('total-hojas');
            const conservadasTexto = document.getElementById('hojas-conservadas');
            const estado = new Map();
            const urlPdf = @json($pdfUrl);

            function actualizarContador() {
                let conservadas = 0;
                document.querySelectorAll('.pagina-card').forEach(function (card) {
                    const numero = parseInt(card.dataset.pagina, 10);
                    const datos = estado.get(numero);
                    if (datos && datos.incluir) {
                        conservadas++;
                    }
                });

                conservadasTexto.textContent = conservadas;
                conservadasTexto.classList.toggle('text-red-600', conservadas === 0);
                conservadasTexto.classList.toggle('text-emerald-700', conservadas > 0);
            }

            if (typeof pdfjsLib === 'undefined') {
                visor.innerHTML = '<div class="col-span-full rounded-lg border-2 border-dashed border-red-300 bg-red-50 p-8 text-center font-bold text-red-700">No se pudo cargar el visor de PDF. Recargue la página.</div>';
                return;
            }

            pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('assets/vendor/js/pdf.worker.min.js') }}";

            let pdf;

            try {
                pdf = await pdfjsLib.getDocument(urlPdf).promise;
            } catch (error) {
                console.error('No se pudo abrir el PDF del acto', error);
                visor.innerHTML = '<div class="col-span-full rounded-lg border-2 border-dashed border-red-300 bg-red-50 p-8 text-center font-bold text-red-700">No se pudo abrir el PDF del acto. Verifique que el archivo exista en el servidor.</div>';
                return;
            }

            totalTexto.textContent = pdf.numPages;

            for (let numero = 1; numero <= pdf.numPages; numero++) {
                const pagina = await pdf.getPage(numero);
                const viewport = pagina.getViewport({ scale: 1.35 });

                const card = plantilla.content.firstElementChild.cloneNode(true);
                card.dataset.pagina = numero;
                card.querySelector('.pagina-num').textContent = numero;

                const canvas = card.querySelector('canvas');
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                await pagina.render({
                    canvasContext: canvas.getContext('2d'),
                    viewport: viewport
                }).promise;

                estado.set(numero, { incluir: true, recorte: null });
                conectarEventosHoja(card, numero);
                visor.appendChild(card);
            }

            document.getElementById('cargando-pdf')?.remove();
            actualizarContador();

            function pintarSeleccion(seleccion, capa, recorte) {
                const ancho = capa.clientWidth;
                const alto = capa.clientHeight;

                if (!recorte) {
                    seleccion.classList.add('hidden');
                    return;
                }

                seleccion.classList.remove('hidden');
                seleccion.style.left = (recorte.x * ancho) + 'px';
                seleccion.style.top = (recorte.y * alto) + 'px';
                seleccion.style.width = (recorte.w * ancho) + 'px';
                seleccion.style.height = (recorte.h * alto) + 'px';
            }

            function conectarEventosHoja(card, numero) {
                const datos = estado.get(numero);
                const checkbox = card.querySelector('.pagina-incluir');
                const capa = card.querySelector('.crop-capa');
                const seleccion = card.querySelector('.crop-seleccion');
                const btnRecortar = card.querySelector('.btn-recortar');
                const btnQuitar = card.querySelector('.btn-quitar-recorte');
                const excluida = card.querySelector('.pagina-excluida');

                let dibujando = false;
                let inicio = null;

                checkbox.addEventListener('change', function () {
                    datos.incluir = checkbox.checked;
                    excluida.classList.toggle('hidden', checkbox.checked);
                    excluida.classList.toggle('flex', !checkbox.checked);
                    card.classList.toggle('opacity-60', !checkbox.checked);
                    actualizarContador();
                });

                btnRecortar.addEventListener('click', function () {
                    capa.classList.remove('hidden');
                    capa.style.pointerEvents = 'auto';
                    capa.classList.add('cursor-crosshair');
                    btnRecortar.classList.add('ring-2', 'ring-emerald-500');
                    pintarSeleccion(seleccion, capa, datos.recorte);
                });

                btnQuitar.addEventListener('click', function () {
                    datos.recorte = null;
                    seleccion.classList.add('hidden');
                    capa.style.pointerEvents = 'none';
                    capa.classList.remove('cursor-crosshair');
                    btnRecortar.classList.remove('ring-2', 'ring-emerald-500');
                    btnQuitar.classList.add('hidden');
                });

                capa.addEventListener('pointerdown', function (evento) {
                    if (capa.style.pointerEvents === 'none') {
                        return;
                    }

                    evento.preventDefault();
                    capa.setPointerCapture(evento.pointerId);

                    const rect = capa.getBoundingClientRect();
                    inicio = {
                        x: Math.min(Math.max(evento.clientX - rect.left, 0), rect.width),
                        y: Math.min(Math.max(evento.clientY - rect.top, 0), rect.height)
                    };
                    dibujando = true;

                    seleccion.classList.remove('hidden');
                    seleccion.style.left = inicio.x + 'px';
                    seleccion.style.top = inicio.y + 'px';
                    seleccion.style.width = '0px';
                    seleccion.style.height = '0px';
                });

                capa.addEventListener('pointermove', function (evento) {
                    if (!dibujando) {
                        return;
                    }

                    const rect = capa.getBoundingClientRect();
                    const actualX = Math.min(Math.max(evento.clientX - rect.left, 0), rect.width);
                    const actualY = Math.min(Math.max(evento.clientY - rect.top, 0), rect.height);

                    seleccion.style.left = Math.min(inicio.x, actualX) + 'px';
                    seleccion.style.top = Math.min(inicio.y, actualY) + 'px';
                    seleccion.style.width = Math.abs(actualX - inicio.x) + 'px';
                    seleccion.style.height = Math.abs(actualY - inicio.y) + 'px';
                });

                function terminarRecorte() {
                    if (!dibujando) {
                        return;
                    }

                    dibujando = false;

                    const rect = capa.getBoundingClientRect();
                    const izquierda = parseFloat(seleccion.style.left) || 0;
                    const arriba = parseFloat(seleccion.style.top) || 0;
                    const ancho = parseFloat(seleccion.style.width) || 0;
                    const alto = parseFloat(seleccion.style.height) || 0;

                    if (ancho < rect.width * 0.03 || alto < rect.height * 0.03) {
                        if (datos.recorte) {
                            pintarSeleccion(seleccion, capa, datos.recorte);
                        } else {
                            seleccion.classList.add('hidden');
                        }

                        capa.style.pointerEvents = 'none';
                        capa.classList.remove('cursor-crosshair');
                        return;
                    }

                    datos.recorte = {
                        x: izquierda / rect.width,
                        y: arriba / rect.height,
                        w: ancho / rect.width,
                        h: alto / rect.height
                    };

                    capa.style.pointerEvents = 'none';
                    capa.classList.remove('cursor-crosshair');
                    btnRecortar.classList.remove('ring-2', 'ring-emerald-500');
                    btnQuitar.classList.remove('hidden');
                }

                capa.addEventListener('pointerup', terminarRecorte);
                capa.addEventListener('pointercancel', terminarRecorte);

                window.addEventListener('resize', function () {
                    if (datos.recorte) {
                        pintarSeleccion(seleccion, capa, datos.recorte);
                    }
                });
            }

            document.getElementById('btn-conservar-todas').addEventListener('click', function () {
                document.querySelectorAll('.pagina-card').forEach(function (card) {
                    const checkbox = card.querySelector('.pagina-incluir');
                    if (!checkbox.checked) {
                        checkbox.checked = true;
                        checkbox.dispatchEvent(new Event('change'));
                    }
                });
            });

            document.getElementById('btn-excluir-todas').addEventListener('click', function () {
                Swal.fire({
                    title: '¿Excluir todas las hojas?',
                    text: 'Deberá conservar al menos una hoja para poder guardar.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#b91c1c',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sí, excluir todas',
                    cancelButtonText: 'Cancelar'
                }).then(function (resultado) {
                    if (!resultado.isConfirmed) {
                        return;
                    }

                    document.querySelectorAll('.pagina-card').forEach(function (card) {
                        const checkbox = card.querySelector('.pagina-incluir');
                        if (checkbox.checked) {
                            checkbox.checked = false;
                            checkbox.dispatchEvent(new Event('change'));
                        }
                    });
                });
            });

            document.getElementById('form-recorte').addEventListener('submit', function (evento) {
                evento.preventDefault();

                const paginas = [];

                document.querySelectorAll('.pagina-card').forEach(function (card) {
                    const numero = parseInt(card.dataset.pagina, 10);
                    const datos = estado.get(numero);

                    if (!datos || !datos.incluir) {
                        return;
                    }

                    const item = { pagina: numero };

                    if (datos.recorte) {
                        item.recorte = datos.recorte;
                    }

                    paginas.push(item);
                });

                if (!paginas.length) {
                    Swal.fire('Atención', 'Debe conservar al menos una hoja del PDF.', 'warning');
                    return;
                }

                const form = evento.target;
                document.getElementById('paginas-json').value = JSON.stringify(paginas);

                const conRecorte = paginas.filter(function (item) {
                    return !!item.recorte;
                }).length;

                Swal.fire({
                    title: '¿Guardar recorte del acto?',
                    html: 'Se conservarán <b>' + paginas.length + '</b> hoja(s)'
                        + (conRecorte ? ' y <b>' + conRecorte + '</b> con zona recortada' : '')
                        + '.<br><span class="text-sm text-slate-500">El PDF actual será reemplazado y se guardará un respaldo.</span>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1e40af',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sí, guardar recorte',
                    cancelButtonText: 'Cancelar'
                }).then(function (resultado) {
                    if (resultado.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
</x-app-layout>
