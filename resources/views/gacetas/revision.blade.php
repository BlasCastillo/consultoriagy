<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Revisión Final: Gaceta Nro. {{ $gaceta->numero }} / {{ $gaceta->anio }} ({{ $gaceta->tipo }})
            </h2>
            <x-action-button :href="route('gacetas.index')"
                action="Volver a Mesa de Control"
                icon="fa-solid fa-arrow-left"
                tone="back"
                align="end" />
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex flex-col xl:flex-row gap-6 items-start">

                {{-- ══════════════════════════════════════════════════════
                COLUMNA IZQUIERDA: VISTA PREVIA LEGAL COMPLETA
                (Idéntica a show.blade.php — portada + sumario + PDF)
                ══════════════════════════════════════════════════════ --}}
                <div class="w-full xl:w-2/3">
                    <div class="mb-3 flex items-center gap-2">
                        <span class="badge badge-warning">
                            Vista Previa del Producto Final
                        </span>
                        <span class="text-xs text-slate-500">Así se verá publicada la Gaceta.</span>
                    </div>

                    {{-- ── HOJA PORTADA (HTML) ── --}}
                    <div class="rounded-lg bg-slate-200 px-4 py-8">
                        <div class="flex w-full flex-col items-center space-y-8">

                            @include('gacetas.partials.portada')

                            {{-- ── PDF ESCANEADO (páginas siguientes a la portada) ── --}}
                            @if($gaceta->archivo_pdf_completo)
                                <div id="pdf-pages-container" class="flex w-full flex-col items-center space-y-8">
                                    <div id="pdf-loading" class="bg-slate-50 py-10 text-center font-bold text-slate-500">
                                        <i class="fa-solid fa-spinner fa-spin mb-3 text-3xl"></i><br>
                                        Cargando páginas del documento escaneado...
                                    </div>
                                </div>
                            @else
                                <div class="relative mx-auto bg-white text-black shadow-2xl"
                                    style="width: 100%; max-width: 850px; padding: 3rem 4rem;">
                                    <div
                                        class="w-full border-2 border-dashed border-amber-300 bg-amber-50 py-16 text-center text-amber-700">
                                        <p class="font-bold">El digitalizador aún no ha subido el documento escaneado.</p>
                                        <p class="mt-1 text-sm text-amber-600">Las páginas del acto aparecerán aquí cuando
                                            el PDF sea subido.</p>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>{{-- /bg-slate-200 --}}
                </div>

                {{-- ══════════════════════════════════════════════════════
                COLUMNA DERECHA: DICTAMEN DE JEFATURA (sticky)
                ══════════════════════════════════════════════════════ --}}
                <div class="w-full xl:w-1/3">
                    <div class="sticky top-6">
                        <div class="card overflow-hidden">

                            {{-- Header del panel --}}
                            <div class="bg-blue-900 px-6 py-4 text-white">
                                <h3 class="text-lg font-bold">
                                    Dictamen de Revisión
                                </h3>
                                <p class="mt-0.5 text-xs text-blue-200">Gaceta Nro. {{ $gaceta->numero }} /
                                    {{ $gaceta->anio }}</p>
                            </div>

                            <div class="p-6 space-y-6">

                                {{-- Aviso --}}
                                <div class="rounded border-l-4 border-blue-500 bg-blue-50 p-4 text-sm text-blue-800">
                                    <p class="mb-1 font-bold">Instrucciones
                                    </p>
                                    <p>Revise la portada, el sumario y las páginas escaneadas a la izquierda. Verifique
                                        que el contenido y el orden de los actos sean correctos antes de aprobar.</p>
                                </div>

                                {{-- Resumen de apartados --}}
                                <div>
                                    <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Instituciones en esta edición</p>
                                    <ul class="space-y-1.5">
                                        @php
                                            $instResumen = $gaceta->solicitudes->groupBy(fn($s) => $s->institucion?->nombre_sumario ?? 'N/A');
                                        @endphp
                                        @forelse($instResumen as $nombre => $sols)
                                            <li
                                                class="flex items-center justify-between rounded border border-slate-100 bg-slate-50 px-3 py-1.5 text-sm">
                                                <span class="truncate pr-2 font-medium text-slate-700">{{ $nombre }}</span>
                                                <span
                                                    class="flex-shrink-0 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-800">
                                                    {{ $sols->count() }} apt.
                                                </span>
                                            </li>
                                        @empty
                                            <li class="text-sm italic text-slate-400">Sin apartados.</li>
                                        @endforelse
                                    </ul>
                                </div>

                                <hr>

                                {{-- ── APROBAR Y PUBLICAR ── --}}
                                <div>
                                    <form method="POST" action="{{ route('gacetas.publicar_oficial', $gaceta->id) }}">
                                        @csrf
                                        <button type="submit"
                                            onclick="event.preventDefault(); const form = this.closest('form'); Swal.fire({ title: '¿Aprobar y publicar oficialmente?', text: 'Esta acción es irreversible y notificará a todas las instituciones.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, aprobar y publicar', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });"
                                            class="btn btn-primary w-full">
                                            Aprobar y Publicar Oficialmente
                                        </button>
                                    </form>
                                    <p class="mt-2 text-center text-xs text-slate-500">La gaceta pasará a estado
                                        <strong>Publicada</strong> y será visible para todas las partes.</p>
                                </div>

                                <hr>

                                {{-- ── RECHAZAR / DEVOLVER ── --}}
                                <div>
                                    <h4 class="mb-3 text-sm font-bold text-red-700">
                                        Rechazar y Devolver
                                    </h4>
                                    <form method="POST" action="{{ route('gacetas.rechazar_escaneo', $gaceta->id) }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="mb-1 block text-sm font-bold text-slate-700">
                                                Motivo de Rechazo <span class="text-red-500">*</span>
                                            </label>
                                            <textarea name="motivo_rechazo_escaneo" rows="4" minlength="5" maxlength="1000" required
                                                class="val-texto-limpio w-full rounded-lg border-slate-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                                placeholder="Indique claramente los errores (ej. página 3 borrosa, falta firma final, orden incorrecto...)">{{ old('motivo_rechazo_escaneo') }}</textarea>
                                        </div>
                                        <button type="submit"
                                            onclick="event.preventDefault(); const form = this.closest('form'); const motivo = form.querySelector('[name=motivo_rechazo_escaneo]').value.trim(); if (!window.validarFormularioSGCJ(form) || motivo.length < 5) { if (motivo.length < 5) Swal.fire('Atención', 'El motivo de rechazo debe tener al menos 5 caracteres.', 'warning'); return; } Swal.fire({ title: '¿Devolver al digitalizador?', text: 'El archivo PDF actual será descartado y el motivo se enviará al digitalizador.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#b91c1c', cancelButtonColor: '#64748b', confirmButtonText: 'Sí, devolver', cancelButtonText: 'Cancelar' }).then((result) => { if (result.isConfirmed) { form.submit(); } });"
                                            class="btn btn-danger w-full">
                                            Devolver a Digitalizador
                                        </button>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
    PDF.JS: misma vista previa que upload_pdf (páginas completas).
    ══════════════════════════════════════════════════════════════════ --}}
    @if($gaceta->archivo_pdf_completo)
        @include('gacetas.partials.preview-pdf', ['pdfUrl' => asset('storage/' . $gaceta->archivo_pdf_completo)])
    @endif

</x-app-layout>